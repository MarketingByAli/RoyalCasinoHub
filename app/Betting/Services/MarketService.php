<?php

namespace App\Betting\Services;

use App\Betting\Enums\MarketFormat;
use App\Betting\Enums\MarketStatus;
use App\Betting\Models\BettingEvent;
use App\Betting\Models\Market;
use App\Betting\Models\MarketParticipant;
use App\Betting\Models\MarketVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class MarketService
{
    public function __construct(
        private MarketStateMachine $stateMachine,
        private MarketReviewService $reviewService,
        private PlayWalletService $walletService,
        private BettingNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(User $creator, BettingEvent $event, array $data): Market
    {
        $this->assertEventEligible($event);

        $format = MarketFormat::from($data['format']);
        $outcomeOptions = $this->resolveOutcomeOptions($format, $data);
        $creatorOutcome = $data['creator_outcome'];
        $stake = (float) $data['stake_amount'];

        if (! in_array($creatorOutcome, $outcomeOptions, true)) {
            throw new RuntimeException('Invalid creator outcome.');
        }

        if ($stake <= 0 || $stake > config('betting.max_stake_per_market')) {
            throw new RuntimeException('Stake amount out of allowed range.');
        }

        $this->assertBettingAllowed($creator, $stake, requireAvailable: true);

        $review = $this->reviewService->review(
            $data['title'],
            $data['description'] ?? '',
            $outcomeOptions
        );

        $market = Market::create([
            'uuid' => (string) Str::uuid(),
            'creator_id' => $creator->id,
            'betting_event_id' => $event->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'format' => $format,
            'outcome_options' => $outcomeOptions,
            'creator_outcome' => $creatorOutcome,
            'stake_amount' => $stake,
            'status' => MarketStatus::Draft,
            'visibility' => 'private_invite',
            'invite_token' => Str::random(48),
            'platform_fee_percent' => 0,
            'betting_close_at' => $event->betting_close_at ?? $event->start_at,
            'dispute_window_hours' => config('betting.default_dispute_window_hours', 24),
            'review_flags' => $review['flags'] ?: null,
            'expires_at' => now()->addDays(config('betting.invite_expiry_days', 7)),
        ]);

        return $market;
    }

    public function submitForReview(Market $market, User $creator): Market
    {
        $this->assertCreator($market, $creator);

        if ($market->status !== MarketStatus::Draft) {
            throw new RuntimeException('Only draft markets can be submitted.');
        }

        $this->assertBettingAllowed($creator, (float) $market->stake_amount, requireAvailable: true);

        if ($this->duplicateExists($market)) {
            throw new RuntimeException('A similar open challenge already exists for this event.');
        }

        $to = ($market->review_flags && count($market->review_flags) > 0)
            ? MarketStatus::PendingReview
            : MarketStatus::Approved;

        return DB::transaction(function () use ($market, $creator, $to) {
            $market = $this->stateMachine->transition($market, MarketStatus::PendingReview, $creator, 'submitted_for_review');

            if ($to === MarketStatus::Approved) {
                $market = $this->stateMachine->transition($market, MarketStatus::Approved, null, 'auto_approved');
                $market = $this->stateMachine->transition($market, MarketStatus::Open, null, 'published');
                $this->reserveCreatorStake($market);
            }

            return $market->fresh();
        });
    }

    public function approve(Market $market, User $admin): Market
    {
        if ($market->status !== MarketStatus::PendingReview) {
            throw new RuntimeException('Market is not pending review.');
        }

        if ($this->duplicateExists($market)) {
            throw new RuntimeException('A similar open challenge already exists for this event.');
        }

        return DB::transaction(function () use ($market, $admin) {
            $market = $this->stateMachine->transition($market, MarketStatus::Approved, $admin, 'admin_approved');
            $market = $this->stateMachine->transition($market, MarketStatus::Open, $admin, 'published');
            $this->reserveCreatorStake($market);

            return $market->fresh();
        });
    }

    public function reject(Market $market, User $admin, string $reason): Market
    {
        if ($market->status !== MarketStatus::PendingReview) {
            throw new RuntimeException('Market is not pending review.');
        }

        $market->rejection_reason = $reason;
        $market->save();

        return $this->stateMachine->transition($market, MarketStatus::Rejected, $admin, $reason);
    }

    public function acceptChallenge(Market $market, User $challenger, ?string $inviteToken = null): Market
    {
        if ($market->status !== MarketStatus::Open) {
            throw new RuntimeException('This challenge is not open for acceptance.');
        }

        if ($market->creator_id === $challenger->id) {
            throw new RuntimeException('You cannot accept your own challenge.');
        }

        if ($market->expires_at && $market->expires_at->isPast()) {
            $this->expireOpenMarket($market);

            throw new RuntimeException('This invitation has expired.');
        }

        if ($market->betting_close_at && $market->betting_close_at->isPast()) {
            throw new RuntimeException('Betting is closed for this challenge.');
        }

        $this->assertInviteAuthorized($market, $inviteToken);

        $market->loadMissing('creator');
        if ($challenger->isBlockedBy($market->creator) || $market->creator->isBlockedBy($challenger)) {
            throw new RuntimeException('You cannot accept this challenge.');
        }

        $stake = (float) $market->stake_amount;
        $this->assertBettingAllowed($challenger, $stake, requireAvailable: true);

        return DB::transaction(function () use ($market, $challenger, $stake, $inviteToken) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if ($market->status !== MarketStatus::Open) {
                throw new RuntimeException('Challenge was already accepted.');
            }

            if ($market->betting_close_at && $market->betting_close_at->isPast()) {
                throw new RuntimeException('Betting is closed for this challenge.');
            }

            $this->assertInviteAuthorized($market, $inviteToken);

            $challengerOutcome = $market->challengerOutcome();
            if (! $challengerOutcome) {
                throw new RuntimeException('Invalid market outcomes.');
            }

            $termsSnapshot = $this->buildTermsSnapshot($market);
            $termsHash = hash('sha256', json_encode($termsSnapshot, JSON_THROW_ON_ERROR));

            $version = MarketVersion::create([
                'betting_market_id' => $market->id,
                'version' => 1,
                'terms_hash' => $termsHash,
                'terms_snapshot' => $termsSnapshot,
            ]);

            // Creator stake should already be reserved on Open; idempotent if present.
            $this->walletService->lockStake(
                $market->creator,
                $stake,
                Market::class,
                $market->id,
                'stake_lock:market:'.$market->id.':creator'
            );

            $this->walletService->lockStake(
                $challenger,
                $stake,
                Market::class,
                $market->id,
                'stake_lock:market:'.$market->id.':challenger'
            );

            MarketParticipant::create([
                'betting_market_id' => $market->id,
                'user_id' => $market->creator_id,
                'role' => 'creator',
                'outcome' => $market->creator_outcome,
                'stake_amount' => $stake,
                'market_version_id' => $version->id,
                'accepted_at' => now(),
            ]);

            MarketParticipant::create([
                'betting_market_id' => $market->id,
                'user_id' => $challenger->id,
                'role' => 'challenger',
                'outcome' => $challengerOutcome,
                'stake_amount' => $stake,
                'market_version_id' => $version->id,
                'accepted_at' => now(),
            ]);

            $market->challenger_id = $challenger->id;
            $market->current_version_id = $version->id;
            $market->save();

            $market = $this->stateMachine->transition($market, MarketStatus::FullyMatched, $challenger, 'challenge_accepted');

            $this->notifications->notify($market->creator, 'bet_accepted', [
                'market_id' => $market->id,
                'market_title' => $market->title,
                'challenger' => $challenger->bettingProfile?->username ?? $challenger->name,
            ]);

            return $market->fresh(['event', 'creator', 'challenger', 'currentVersion', 'participants']);
        });
    }

    public function declineChallenge(Market $market, User $user): Market
    {
        if ($market->creator_id === $user->id && $market->status === MarketStatus::Open) {
            return $this->cancelBeforeMatch($market, $user);
        }

        throw new RuntimeException('Cannot decline this challenge.');
    }

    public function cancelBeforeMatch(Market $market, User $creator): Market
    {
        $this->assertCreator($market, $creator);

        if (! in_array($market->status, [MarketStatus::Draft, MarketStatus::PendingReview, MarketStatus::Approved, MarketStatus::Open], true)) {
            throw new RuntimeException('Cannot cancel market in current state.');
        }

        return DB::transaction(function () use ($market, $creator) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if (! in_array($market->status, [MarketStatus::Draft, MarketStatus::PendingReview, MarketStatus::Approved, MarketStatus::Open], true)) {
                throw new RuntimeException('Cannot cancel market in current state.');
            }

            if ($market->status === MarketStatus::Open) {
                $this->releaseCreatorReserve($market);
            }

            return $this->stateMachine->transition($market, MarketStatus::Cancelled, $creator, 'cancelled_before_match');
        });
    }

    public function expireOpenMarket(Market $market): Market
    {
        return DB::transaction(function () use ($market) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if ($market->status !== MarketStatus::Open) {
                return $market;
            }

            $this->releaseCreatorReserve($market);

            return $this->stateMachine->transition($market, MarketStatus::Expired, null, 'invite_expired');
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function buildTermsSnapshot(Market $market): array
    {
        $market->loadMissing('event');

        return [
            'market_id' => $market->id,
            'uuid' => $market->uuid,
            'title' => $market->title,
            'description' => $market->description,
            'format' => $market->format->value,
            'outcome_options' => $market->outcome_options,
            'creator_outcome' => $market->creator_outcome,
            'stake_amount' => (string) $market->stake_amount,
            'platform_fee_percent' => (string) $market->platform_fee_percent,
            'event' => [
                'id' => $market->event->id,
                'title' => $market->event->title,
                'start_at' => $market->event->start_at?->toIso8601String(),
                'settlement_source' => $market->event->settlement_source,
            ],
            'betting_close_at' => $market->betting_close_at?->toIso8601String(),
            'dispute_window_hours' => $market->dispute_window_hours,
            'locked_at' => now()->toIso8601String(),
        ];
    }

    private function reserveCreatorStake(Market $market): void
    {
        $market->loadMissing('creator');

        $this->walletService->lockStake(
            $market->creator,
            (float) $market->stake_amount,
            Market::class,
            $market->id,
            'stake_lock:market:'.$market->id.':creator'
        );
    }

    private function releaseCreatorReserve(Market $market): void
    {
        $market->loadMissing('creator');

        if (! $this->walletService->hasActiveCreatorReserve($market)) {
            return;
        }

        $this->walletService->releaseStake(
            $market->creator,
            (float) $market->stake_amount,
            Market::class,
            $market->id,
            'stake_release:market:'.$market->id.':creator'
        );
    }

    private function assertInviteAuthorized(Market $market, ?string $inviteToken): void
    {
        if ($market->visibility !== 'private_invite') {
            return;
        }

        if (! is_string($inviteToken) || $inviteToken === '' || ! hash_equals((string) $market->invite_token, $inviteToken)) {
            throw new RuntimeException('A valid invite link is required to accept this challenge.');
        }
    }

    private function assertCreator(Market $market, User $user): void
    {
        if ($market->creator_id !== $user->id) {
            throw new RuntimeException('Only the creator can perform this action.');
        }
    }

    private function assertEventEligible(BettingEvent $event): void
    {
        if (! in_array($event->status, ['scheduled', 'in_progress'], true)) {
            throw new RuntimeException('This event is not available for betting.');
        }

        if ($event->start_at && $event->start_at->isPast()) {
            throw new RuntimeException('This event has already started.');
        }
    }

    private function assertBettingAllowed(User $user, float $additionalStake, bool $requireAvailable = false): void
    {
        if (! $user->hasVerifiedEmail()) {
            throw new RuntimeException('Email verification required for betting.');
        }

        $profile = $user->bettingProfile;
        if (! $profile || ! in_array($profile->account_state->value, ['play_only', 'verified'], true)) {
            throw new RuntimeException('Account not eligible for betting.');
        }

        $exposure = $this->walletService->totalExposure($user);
        $max = (float) config('betting.max_open_liability_per_user', 20000);

        if ($exposure + $additionalStake > $max) {
            throw new RuntimeException('Maximum exposure limit exceeded.');
        }

        if ($requireAvailable) {
            $wallet = $this->walletService->getOrCreateWallet($user);
            if (bccomp((string) $wallet->available, (string) $additionalStake, 2) < 0) {
                throw new RuntimeException('Insufficient available balance for this stake.');
            }
        }
    }

    private function duplicateExists(Market $market): bool
    {
        return Market::query()
            ->where('betting_event_id', $market->betting_event_id)
            ->where('creator_id', $market->creator_id)
            ->where('creator_outcome', $market->creator_outcome)
            ->where('id', '!=', $market->id)
            ->whereIn('status', ['open', 'fully_matched', 'locked', 'in_progress'])
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    private function resolveOutcomeOptions(MarketFormat $format, array $data): array
    {
        return match ($format) {
            MarketFormat::YesNo => ['Yes', 'No'],
            MarketFormat::TeamVsTeam => [
                $data['team_a'] ?? 'Team A',
                $data['team_b'] ?? 'Team B',
            ],
        };
    }
}
