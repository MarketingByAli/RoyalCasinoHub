<?php

namespace App\Betting\Services;

use App\Betting\Enums\AccountState;
use App\Betting\Enums\LedgerEntryType;
use App\Betting\Models\LedgerEntry;
use App\Betting\Models\RgAction;
use App\Betting\Models\RgLimit;
use App\Models\User;
use RuntimeException;

class ResponsibleGamblingService
{
    public function __construct(
        private BettingNotificationService $notifications,
        private BettingAuditService $audit,
    ) {}

    public function assertCanStake(User $user, float $stake): void
    {
        $profile = $user->bettingProfile;
        if ($profile && in_array($profile->account_state, [
            AccountState::SelfExcluded,
            AccountState::BettingRestricted,
            AccountState::TemporarilySuspended,
            AccountState::PermanentlyClosed,
        ], true)) {
            throw new RuntimeException('Your account is restricted from betting.');
        }

        $coolOff = $this->activeAction($user, 'cool_off');
        if ($coolOff) {
            throw new RuntimeException('Cooling-off period is active until '.$coolOff->ends_at?->toDateTimeString().'.');
        }

        $selfExclude = $this->activeAction($user, 'self_exclusion');
        if ($selfExclude) {
            throw new RuntimeException('Self-exclusion is active on your account.');
        }

        $limits = RgLimit::where('user_id', $user->id)->first();
        if (! $limits) {
            return;
        }

        if ($limits->daily_stake_limit !== null) {
            $daily = $this->stakedSince($user, now()->startOfDay());
            if ($daily + $stake > (float) $limits->daily_stake_limit) {
                $this->notifications->notify($user, 'rg_limit_hit', ['limit' => 'daily']);
                throw new RuntimeException('Daily stake limit exceeded.');
            }
        }

        if ($limits->weekly_stake_limit !== null) {
            $weekly = $this->stakedSince($user, now()->startOfWeek());
            if ($weekly + $stake > (float) $limits->weekly_stake_limit) {
                $this->notifications->notify($user, 'rg_limit_hit', ['limit' => 'weekly']);
                throw new RuntimeException('Weekly stake limit exceeded.');
            }
        }
    }

    public function upsertLimits(User $user, ?float $daily, ?float $weekly): RgLimit
    {
        return RgLimit::updateOrCreate(
            ['user_id' => $user->id],
            [
                'daily_stake_limit' => $daily,
                'weekly_stake_limit' => $weekly,
            ]
        );
    }

    public function startCoolOff(User $user, int $hours, ?User $actor = null, ?string $reason = null): RgAction
    {
        $action = RgAction::create([
            'user_id' => $user->id,
            'type' => 'cool_off',
            'starts_at' => now(),
            'ends_at' => now()->addHours($hours),
            'reason' => $reason,
            'created_by' => $actor?->id ?? $user->id,
        ]);

        $profile = $user->bettingProfile;
        if ($profile) {
            $profile->account_state = AccountState::BettingRestricted;
            $profile->save();
        }

        $this->audit->log($user, null, 'rg_cool_off', $actor, $reason, ['hours' => $hours, 'action_id' => $action->id]);

        return $action;
    }

    public function startSelfExclusion(User $user, ?\DateTimeInterface $endsAt = null, ?User $actor = null, ?string $reason = null): RgAction
    {
        $action = RgAction::create([
            'user_id' => $user->id,
            'type' => 'self_exclusion',
            'starts_at' => now(),
            'ends_at' => $endsAt,
            'reason' => $reason,
            'created_by' => $actor?->id ?? $user->id,
        ]);

        $profile = $user->bettingProfile;
        if ($profile) {
            $profile->account_state = AccountState::SelfExcluded;
            $profile->save();
        }

        $this->audit->log($user, null, 'rg_self_exclusion', $actor, $reason, ['action_id' => $action->id]);

        return $action;
    }

    public function clearExpiredRestrictions(User $user): void
    {
        $profile = $user->bettingProfile;
        if (! $profile) {
            return;
        }

        if ($this->activeAction($user, 'self_exclusion') || $this->activeAction($user, 'cool_off')) {
            return;
        }

        if (in_array($profile->account_state, [AccountState::SelfExcluded, AccountState::BettingRestricted], true)) {
            if ($user->hasVerifiedEmail()) {
                $profile->account_state = AccountState::PlayOnly;
                $profile->save();
            }
        }
    }

    public function activeAction(User $user, string $type): ?RgAction
    {
        return RgAction::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->latest('id')
            ->first();
    }

    private function stakedSince(User $user, \DateTimeInterface $since): float
    {
        $wallet = $user->bettingWallet;
        if (! $wallet) {
            return 0.0;
        }

        return (float) LedgerEntry::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', LedgerEntryType::StakeLock)
            ->where('created_at', '>=', $since)
            ->sum('amount');
    }
}
