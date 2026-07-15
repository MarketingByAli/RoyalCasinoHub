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

        $this->assertBettingAllowed($creator, $stake);

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

        if ($this->duplicateExists($market)) {
            throw new RuntimeException('A similar open challenge already exists for this event.');
        }

        $to = ($market->review_flags && count($market->review_flags) > 0)
            ? MarketStatus::PendingReview
            : MarketStatus::Approved;

        $market = $this->stateMachine->transition($market, MarketStatus::PendingReview, $creator, 'submitted_for_review');

        if ($to === MarketStatus::Approved) {
            $market = $this->stateMachine->transition($market, MarketStatus::Approved, null, 'auto_approved');
            $market = $this->stateMachine->transition($market, MarketStatus::Open, null, 'published');
        }

        return $market->fresh();
    }

    public function approve(Market $market, User $admin): Market
    {
        if ($market->status !== MarketStatus::PendingReview) {
            throw new RuntimeException('Market is not pending review.');
        }

        $market = $this->stateMachine->transition($market, MarketStatus::Approved, $admin, 'admin_approved');

        return $this->stateMachine->transition($market, MarketStatus::Open, $admin, 'published');
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

    public function acceptChallenge(Market $market, User $challenger): Market
    {
        if ($market->status !== MarketStatus::Open) {
            throw new RuntimeException('This challenge is not open for acceptance.');
        }

        if ($market->creator_id === $challenger->id) {
            throw new RuntimeException('You cannot accept your own challenge.');
        }

        if ($market->expires_at && $market->expires_at->isPast()) {
            $this->stateMachine->transition($market, MarketStatus::Expired, null, 'invite_expired');
            throw new RuntimeException('This invitation has expired.');
        }

        $stake = (float) $market->stake_amount;
        $this->assertBettingAllowed($challenger, $stake);

        return DB::transaction(function () use ($market, $challenger, $stake) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if ($market->status !== MarketStatus::Open) {
                throw new RuntimeException('Challenge was already accepted.');
            }

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
            return $this->stateMachine->transition($market, MarketStatus::Cancelled, $user, 'creator_cancelled');
        }

        throw new RuntimeException('Cannot decline this challenge.');
    }

    public function cancelBeforeMatch(Market $market, User $creator): Market
    {
        $this->assertCreator($market, $creator);

        if (! in_array($market->status, [MarketStatus::Draft, MarketStatus::PendingReview, MarketStatus::Approved, MarketStatus::Open], true)) {
            throw new RuntimeException('Cannot cancel market in current state.');
        }

        return $this->stateMachine->transition($market, MarketStatus::Cancelled, $creator, 'cancelled_before_match');
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

    private function assertCreator(Market $market, User $user): void
    {
        if ($market->creator_id !== $user->id) {
            throw new RuntimeException('Only the creator can perform this action.');
        }
    }

    private function assertBettingAllowed(User $user, float $additionalStake): void
    {
        if (! $user->hasVerifiedEmail()) {
            throw new RuntimeException('Email verification required for betting.');
        }

        $profile = $user->bettingProfile;
        if (! $profile || ! in_array($profile->account_state->value, ['play_only', 'verified'], true)) {
            throw new RuntimeException('Account not eligible for betting.');
        }

        $openLiability = $this->walletService->openLiability($user);
        $max = config('betting.max_open_liability_per_user', 20000);

        if ($openLiability + $additionalStake > $max) {
            throw new RuntimeException('Maximum exposure limit exceeded.');
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
