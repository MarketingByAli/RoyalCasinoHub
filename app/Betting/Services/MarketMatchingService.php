<?php

namespace App\Betting\Services;

use App\Betting\Enums\MarketStatus;
use App\Betting\Enums\ParticipantStatus;
use App\Betting\Models\Market;
use App\Betting\Models\MarketParticipant;
use App\Betting\Models\MarketVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MarketMatchingService
{
    public function __construct(
        private MarketStateMachine $stateMachine,
        private MarketService $marketService,
        private PlayWalletService $walletService,
        private BettingNotificationService $notifications,
        private ResponsibleGamblingService $rg,
    ) {}

    public function join(Market $market, User $user, string $outcome, ?string $inviteToken = null, ?float $proposedStake = null): Market
    {
        if (! in_array($market->status, [MarketStatus::Open, MarketStatus::PartiallyMatched], true)) {
            throw new RuntimeException('This challenge is not open for joining.');
        }

        if ($market->creator_id === $user->id) {
            throw new RuntimeException('You cannot join your own challenge.');
        }

        if ($market->expires_at && $market->expires_at->isPast()) {
            throw new RuntimeException('This invitation has expired.');
        }

        if ($market->betting_close_at && $market->betting_close_at->isPast()) {
            throw new RuntimeException('Betting is closed for this challenge.');
        }

        if (! in_array($outcome, $market->outcome_options ?? [], true)) {
            throw new RuntimeException('Invalid outcome selection.');
        }

        $this->assertInviteAuthorized($market, $inviteToken);

        $market->loadMissing('creator');
        if ($user->isBlockedBy($market->creator) || $market->creator->isBlockedBy($user)) {
            throw new RuntimeException('You cannot join this challenge.');
        }

        $stake = (float) ($proposedStake ?? $market->stake_amount);
        if ($proposedStake !== null && abs($proposedStake - (float) $market->stake_amount) > 0.001) {
            return $this->submitCounterOffer($market, $user, $outcome, $stake, $inviteToken);
        }

        $this->rg->assertCanStake($user, $stake);
        $this->marketService->assertBettingAllowedPublic($user, $stake, requireAvailable: true);

        return DB::transaction(function () use ($market, $user, $outcome, $stake, $inviteToken) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if (! in_array($market->status, [MarketStatus::Open, MarketStatus::PartiallyMatched], true)) {
                throw new RuntimeException('Challenge is no longer joinable.');
            }

            $this->assertInviteAuthorized($market, $inviteToken);

            if ($market->participants()->where('user_id', $user->id)->where('status', '!=', ParticipantStatus::Withdrawn->value)->exists()) {
                throw new RuntimeException('You already joined this challenge.');
            }

            $activeCount = $market->participants()->where('status', ParticipantStatus::Active)->count();
            // Creator reserve counts as one seat once open.
            $seatsTaken = max($activeCount, $market->status === MarketStatus::Open ? 1 : $activeCount);

            if ($seatsTaken >= (int) $market->participant_cap) {
                throw new RuntimeException('This challenge is full.');
            }

            $this->walletService->lockStake(
                $user,
                $stake,
                Market::class,
                $market->id,
                $this->walletService->stakeLockKey($market, $user)
            );

            MarketParticipant::create([
                'betting_market_id' => $market->id,
                'user_id' => $user->id,
                'role' => 'challenger',
                'status' => ParticipantStatus::Active,
                'outcome' => $outcome,
                'stake_amount' => $stake,
                'accepted_at' => now(),
            ]);

            if (! $market->challenger_id) {
                $market->challenger_id = $user->id;
                $market->save();
            }

            $this->notifications->notify($market->creator, 'challenge_joined', [
                'market_id' => $market->id,
                'market_title' => $market->title,
                'joiner' => $user->bettingProfile?->username ?? $user->name,
            ]);

            return $this->advanceMatchState($market);
        });
    }

    public function withdraw(Market $market, User $user): Market
    {
        return DB::transaction(function () use ($market, $user) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if (! in_array($market->status, [MarketStatus::Open, MarketStatus::PartiallyMatched], true)) {
                throw new RuntimeException('Cannot withdraw after the market is matched.');
            }

            $participant = MarketParticipant::query()
                ->where('betting_market_id', $market->id)
                ->where('user_id', $user->id)
                ->whereIn('status', [ParticipantStatus::Active, ParticipantStatus::PendingCounter])
                ->lockForUpdate()
                ->first();

            if (! $participant || $participant->role === 'creator') {
                throw new RuntimeException('No join to withdraw.');
            }

            if ($participant->status === ParticipantStatus::Active
                && $this->walletService->hasStakeLockForMarket($user, $market)) {
                $this->walletService->releaseStake(
                    $user,
                    (float) $participant->stake_amount,
                    Market::class,
                    $market->id,
                    $this->walletService->stakeReleaseKey($market, $user)
                );
            }

            $participant->status = ParticipantStatus::Withdrawn;
            $participant->save();

            return $this->advanceMatchState($market->fresh());
        });
    }

    public function submitCounterOffer(Market $market, User $user, string $outcome, float $proposedStake, ?string $inviteToken = null): Market
    {
        if ($proposedStake <= 0 || $proposedStake > config('betting.max_stake_per_market')) {
            throw new RuntimeException('Proposed stake out of allowed range.');
        }

        $this->assertInviteAuthorized($market, $inviteToken);
        $this->rg->assertCanStake($user, $proposedStake);
        $this->marketService->assertBettingAllowedPublic($user, $proposedStake, requireAvailable: true);

        return DB::transaction(function () use ($market, $user, $outcome, $proposedStake, $inviteToken) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();
            $this->assertInviteAuthorized($market, $inviteToken);

            if (! in_array($market->status, [MarketStatus::Open, MarketStatus::PartiallyMatched], true)) {
                throw new RuntimeException('Challenge is not open for counter-offers.');
            }

            MarketParticipant::updateOrCreate(
                [
                    'betting_market_id' => $market->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => 'challenger',
                    'status' => ParticipantStatus::PendingCounter,
                    'outcome' => $outcome,
                    'stake_amount' => $market->stake_amount,
                    'proposed_stake_amount' => $proposedStake,
                    'proposed_outcome' => $outcome,
                ]
            );

            $this->notifications->notify($market->creator, 'counter_offer_received', [
                'market_id' => $market->id,
                'market_title' => $market->title,
                'from' => $user->bettingProfile?->username ?? $user->name,
                'proposed_stake' => $proposedStake,
            ]);

            return $market->fresh(['participants']);
        });
    }

    public function acceptCounterOffer(Market $market, User $creator, User $joiner): Market
    {
        if ($market->creator_id !== $creator->id) {
            throw new RuntimeException('Only the creator can accept counter-offers.');
        }

        return DB::transaction(function () use ($market, $creator, $joiner) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();
            $participant = MarketParticipant::query()
                ->where('betting_market_id', $market->id)
                ->where('user_id', $joiner->id)
                ->where('status', ParticipantStatus::PendingCounter)
                ->lockForUpdate()
                ->firstOrFail();

            $stake = (float) $participant->proposed_stake_amount;
            $outcome = $participant->proposed_outcome ?? $participant->outcome;

            $this->rg->assertCanStake($joiner, $stake);
            $this->marketService->assertBettingAllowedPublic($joiner, $stake, requireAvailable: true);

            // Adjust creator reserve if stake changed.
            $original = (float) $market->stake_amount;
            if (abs($stake - $original) > 0.001) {
                if ($stake > $original) {
                    $delta = $stake - $original;
                    $this->walletService->lockStake(
                        $creator,
                        $delta,
                        Market::class,
                        $market->id,
                        'stake_lock:market:'.$market->id.':user:'.$creator->id.':counter_delta'
                    );
                }
                $market->stake_amount = $stake;
                $market->save();
            }

            $this->walletService->lockStake(
                $joiner,
                $stake,
                Market::class,
                $market->id,
                $this->walletService->stakeLockKey($market, $joiner)
            );

            $participant->status = ParticipantStatus::Active;
            $participant->outcome = $outcome;
            $participant->stake_amount = $stake;
            $participant->accepted_at = now();
            $participant->save();

            if (! $market->challenger_id) {
                $market->challenger_id = $joiner->id;
                $market->save();
            }

            $this->notifications->notify($joiner, 'counter_offer_accepted', [
                'market_id' => $market->id,
                'market_title' => $market->title,
            ]);

            return $this->advanceMatchState($market->fresh());
        });
    }

    public function rejectCounterOffer(Market $market, User $creator, User $joiner): Market
    {
        if ($market->creator_id !== $creator->id) {
            throw new RuntimeException('Only the creator can reject counter-offers.');
        }

        $participant = MarketParticipant::query()
            ->where('betting_market_id', $market->id)
            ->where('user_id', $joiner->id)
            ->where('status', ParticipantStatus::PendingCounter)
            ->firstOrFail();

        $participant->status = ParticipantStatus::Withdrawn;
        $participant->save();

        return $market->fresh(['participants']);
    }

    private function advanceMatchState(Market $market): Market
    {
        $market->loadMissing('creator');

        $activeJoiners = $market->participants()
            ->where('status', ParticipantStatus::Active)
            ->where('role', '!=', 'creator')
            ->count();

        // Ensure creator participant row exists when first joiner arrives.
        if ($activeJoiners > 0 && ! $market->participants()->where('user_id', $market->creator_id)->where('status', ParticipantStatus::Active)->exists()) {
            MarketParticipant::create([
                'betting_market_id' => $market->id,
                'user_id' => $market->creator_id,
                'role' => 'creator',
                'status' => ParticipantStatus::Active,
                'outcome' => $market->creator_outcome,
                'stake_amount' => $market->stake_amount,
                'accepted_at' => now(),
            ]);
        }

        $activeTotal = $market->participants()->where('status', ParticipantStatus::Active)->count();
        $cap = (int) $market->participant_cap;

        if ($activeTotal >= $cap) {
            if (! $market->current_version_id) {
                $termsSnapshot = $this->marketService->buildTermsSnapshot($market);
                $version = MarketVersion::create([
                    'betting_market_id' => $market->id,
                    'version' => 1,
                    'terms_hash' => hash('sha256', json_encode($termsSnapshot, JSON_THROW_ON_ERROR)),
                    'terms_snapshot' => $termsSnapshot,
                ]);
                $market->current_version_id = $version->id;
                $market->save();
                $market->participants()->where('status', ParticipantStatus::Active)->update(['market_version_id' => $version->id]);
            }

            if ($market->status !== MarketStatus::FullyMatched) {
                $market = $this->stateMachine->transition($market, MarketStatus::FullyMatched, null, 'seats_filled');
            }

            return $market->fresh(['event', 'creator', 'challenger', 'currentVersion', 'participants']);
        }

        if ($activeJoiners > 0 && $market->status === MarketStatus::Open) {
            $market = $this->stateMachine->transition($market, MarketStatus::PartiallyMatched, null, 'partial_fill');
        }

        if ($activeJoiners === 0 && $market->status === MarketStatus::PartiallyMatched) {
            $market = $this->stateMachine->transition($market, MarketStatus::Open, null, 'reopened_after_withdraw');
        }

        return $market->fresh(['event', 'creator', 'challenger', 'participants']);
    }

    private function assertInviteAuthorized(Market $market, ?string $inviteToken): void
    {
        if ($market->visibility !== 'private_invite') {
            return;
        }

        if (! is_string($inviteToken) || $inviteToken === '' || ! hash_equals((string) $market->invite_token, $inviteToken)) {
            throw new RuntimeException('A valid invite link is required to join this challenge.');
        }
    }
}
