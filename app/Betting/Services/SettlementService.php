<?php

namespace App\Betting\Services;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\BettingEvent;
use App\Betting\Models\Dispute;
use App\Betting\Models\Market;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SettlementService
{
    public function __construct(
        private MarketStateMachine $stateMachine,
        private PlayWalletService $walletService,
        private BettingNotificationService $notifications,
    ) {}

    public function publishEventResult(BettingEvent $event, string $winningOutcome, User $admin): BettingEvent
    {
        return DB::transaction(function () use ($event, $winningOutcome, $admin) {
            $markets = Market::query()
                ->where('betting_event_id', $event->id)
                ->whereIn('status', [
                    MarketStatus::FullyMatched,
                    MarketStatus::Locked,
                    MarketStatus::InProgress,
                    MarketStatus::PendingResult,
                ])
                ->lockForUpdate()
                ->get();

            $incompatible = $markets->filter(
                fn (Market $market) => ! in_array($winningOutcome, $market->outcome_options ?? [], true)
            );

            if ($incompatible->isNotEmpty()) {
                $ids = $incompatible->pluck('id')->implode(', ');

                throw new RuntimeException(
                    "Winning outcome is not valid for market(s): {$ids}. Publish results per market instead."
                );
            }

            $event->winning_outcome = $winningOutcome;
            $event->result_published_at = now();
            $event->status = 'completed';
            $event->save();

            foreach ($markets as $market) {
                $this->publishMarketResult($market, $winningOutcome, $admin);
            }

            return $event->fresh();
        });
    }

    public function publishMarketResult(Market $market, string $winningOutcome, ?User $admin = null): Market
    {
        return DB::transaction(function () use ($market, $winningOutcome, $admin) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if (! in_array($winningOutcome, $market->outcome_options ?? [], true)) {
                throw new RuntimeException('Winning outcome must be one of the market outcomes.');
            }

            if (! $market->isMatched()) {
                throw new RuntimeException('Market must be matched before publishing result.');
            }

            if (in_array($market->status, [MarketStatus::DisputeWindow, MarketStatus::UnderDispute, MarketStatus::Settled, MarketStatus::Voided], true)) {
                throw new RuntimeException('Result already published for this market.');
            }

            $market->winning_outcome = $winningOutcome;
            $market->save();

            if ($market->status === MarketStatus::FullyMatched) {
                $market = $this->stateMachine->transition($market, MarketStatus::Locked, $admin, 'event_started');
                $market = $this->stateMachine->transition($market, MarketStatus::InProgress, null, 'in_progress');
                $market = $this->stateMachine->transition($market, MarketStatus::PendingResult, null, 'awaiting_result');
            }

            $market = $this->stateMachine->transition($market, MarketStatus::ResultPublished, $admin, 'result_published');

            $market->dispute_window_ends_at = now()->addHours($market->dispute_window_hours);
            $market->save();

            $market = $this->stateMachine->transition($market, MarketStatus::DisputeWindow, null, 'dispute_window_opened');

            foreach ($market->participants()->with('user')->get() as $participant) {
                $this->notifications->notify($participant->user, 'result_published', [
                    'market_id' => $market->id,
                    'market_title' => $market->title,
                    'winning_outcome' => $winningOutcome,
                ]);
            }

            return $market->fresh();
        });
    }

    public function finalizeAfterDisputeWindow(Market $market): Market
    {
        return DB::transaction(function () use ($market) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if ($market->status !== MarketStatus::DisputeWindow) {
                throw new RuntimeException('Market is not in dispute window.');
            }

            if ($market->dispute_window_ends_at && $market->dispute_window_ends_at->isFuture()) {
                throw new RuntimeException('Dispute window still open.');
            }

            if ($market->disputes()->where('status', 'open')->exists()) {
                return $this->stateMachine->transition($market, MarketStatus::UnderDispute, null, 'open_dispute_exists');
            }

            return $this->settleMarket($market, force: true);
        });
    }

    /**
     * Finalize overdue dispute windows when a market is viewed (scheduler fallback).
     */
    public function ensureDisputeWindowFinalized(Market $market): Market
    {
        if (
            $market->status === MarketStatus::DisputeWindow
            && $market->dispute_window_ends_at
            && $market->dispute_window_ends_at->isPast()
        ) {
            try {
                return $this->finalizeAfterDisputeWindow($market);
            } catch (\Throwable) {
                return $market->fresh();
            }
        }

        return $market;
    }

    public function settleMarket(Market $market, bool $force = false): Market
    {
        if (! in_array($market->status, [MarketStatus::DisputeWindow, MarketStatus::UnderDispute], true)) {
            throw new RuntimeException('Market cannot be settled from current state.');
        }

        return DB::transaction(function () use ($market, $force) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if ($market->status === MarketStatus::Settled) {
                return $market;
            }

            if (! in_array($market->status, [MarketStatus::DisputeWindow, MarketStatus::UnderDispute], true)) {
                throw new RuntimeException('Market cannot be settled from current state.');
            }

            if (
                $market->status === MarketStatus::DisputeWindow
                && ! $force
                && $market->dispute_window_ends_at
                && $market->dispute_window_ends_at->isFuture()
            ) {
                throw new RuntimeException('Dispute window still open. Use force settle to override.');
            }

            if ($market->disputes()->where('status', 'open')->exists()) {
                throw new RuntimeException('Cannot settle market with open disputes.');
            }

            $winningOutcome = $market->winning_outcome;
            if (! $winningOutcome) {
                throw new RuntimeException('No winning outcome set.');
            }

            if (! in_array($winningOutcome, $market->outcome_options ?? [], true)) {
                return $this->voidMarket($market, null, 'invalid_winning_outcome');
            }

            $stake = (float) $market->stake_amount;
            $participants = $market->participants()->with('user')->get();

            $winners = $participants->where('outcome', $winningOutcome);
            $losers = $participants->where('outcome', '!=', $winningOutcome);

            if ($winners->isEmpty() && $losers->isNotEmpty()) {
                return $this->voidMarket($market, null, 'no_matching_outcome');
            }

            foreach ($losers as $loser) {
                $this->walletService->settleLoser(
                    $loser->user,
                    $stake,
                    Market::class,
                    $market->id,
                    'settle_debit:market:'.$market->id.':user:'.$loser->user_id
                );
            }

            $pot = $stake * $losers->count();

            foreach ($winners as $winner) {
                $share = $pot / max(1, $winners->count());
                $totalPayout = bcadd((string) $share, (string) $stake, 2);

                $this->walletService->settleWinner(
                    $winner->user,
                    $stake,
                    (float) $totalPayout,
                    Market::class,
                    $market->id,
                    'settle_credit:market:'.$market->id.':user:'.$winner->user_id
                );

                $this->notifications->notify($winner->user, 'bet_settled', [
                    'market_id' => $market->id,
                    'market_title' => $market->title,
                    'result' => 'won',
                ]);
            }

            foreach ($losers as $loser) {
                $this->notifications->notify($loser->user, 'bet_settled', [
                    'market_id' => $market->id,
                    'market_title' => $market->title,
                    'result' => 'lost',
                ]);
            }

            return $this->stateMachine->transition($market, MarketStatus::Settled, null, 'settled');
        });
    }

    public function voidMarket(Market $market, ?User $admin, string $reason): Market
    {
        return DB::transaction(function () use ($market, $admin, $reason) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();
            $stake = (float) $market->stake_amount;

            $this->closeOpenDisputes($market, $admin, 'void', $reason);

            foreach ($market->participants()->with('user')->get() as $participant) {
                $role = $participant->role === 'creator' ? 'creator' : 'challenger';
                if ($this->walletService->hasStakeLockForMarket($participant->user, $market, $role)) {
                    $this->walletService->voidRefund(
                        $participant->user,
                        $stake,
                        Market::class,
                        $market->id,
                        'void_refund:market:'.$market->id.':user:'.$participant->user_id
                    );
                }
            }

            return $this->stateMachine->transition($market, MarketStatus::Voided, $admin, $reason);
        });
    }

    public function openDispute(Market $market, User $user, string $reasonCategory, ?string $explanation): Dispute
    {
        return DB::transaction(function () use ($market, $user, $reasonCategory, $explanation) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if ($market->status !== MarketStatus::DisputeWindow) {
                throw new RuntimeException('Disputes can only be opened during the dispute window.');
            }

            if ($market->dispute_window_ends_at && $market->dispute_window_ends_at->isPast()) {
                throw new RuntimeException('Dispute window has closed.');
            }

            if (! $market->participants()->where('user_id', $user->id)->exists()) {
                throw new RuntimeException('Only participants can dispute.');
            }

            if ($market->disputes()->where('user_id', $user->id)->where('status', 'open')->exists()) {
                throw new RuntimeException('You already have an open dispute on this market.');
            }

            $dispute = Dispute::create([
                'betting_market_id' => $market->id,
                'user_id' => $user->id,
                'reason_category' => $reasonCategory,
                'explanation' => $explanation,
                'status' => 'open',
            ]);

            $this->stateMachine->transition($market, MarketStatus::UnderDispute, $user, 'dispute_opened');

            foreach ($market->participants()->with('user')->get() as $participant) {
                $this->notifications->notify($participant->user, 'dispute_opened', [
                    'market_id' => $market->id,
                    'market_title' => $market->title,
                    'opened_by' => $user->bettingProfile?->username ?? $user->name,
                ]);
            }

            return $dispute;
        });
    }

    public function resolveDispute(Dispute $dispute, User $admin, string $resolution, ?string $note): Market
    {
        if (! in_array($resolution, ['confirm', 'void'], true)) {
            throw new RuntimeException('Unknown resolution.');
        }

        $result = DB::transaction(function () use ($dispute, $admin, $resolution, $note) {
            $dispute = Dispute::where('id', $dispute->id)->lockForUpdate()->firstOrFail();

            if ($dispute->status !== 'open') {
                throw new RuntimeException('Dispute is already resolved.');
            }

            $market = Market::where('id', $dispute->betting_market_id)->lockForUpdate()->firstOrFail();

            $this->closeOpenDisputes($market, $admin, $resolution, $note);

            return match ($resolution) {
                'confirm' => $this->settleMarket($market->fresh(), force: true),
                'void' => $this->voidMarket($market->fresh(), $admin, 'dispute_void'),
            };
        });

        foreach ($result->participants()->with('user')->get() as $participant) {
            $this->notifications->notify($participant->user, 'dispute_resolved', [
                'market_id' => $result->id,
                'market_title' => $result->title,
                'resolution' => $resolution,
            ]);
        }

        return $result;
    }

    public function advanceMarketsForEventStart(BettingEvent $event): void
    {
        Market::query()
            ->where('betting_event_id', $event->id)
            ->where('status', MarketStatus::FullyMatched)
            ->each(function (Market $market) {
                $this->stateMachine->transition($market, MarketStatus::Locked, null, 'event_started');
                $this->stateMachine->transition($market, MarketStatus::InProgress, null, 'in_progress');
                $this->stateMachine->transition($market, MarketStatus::PendingResult, null, 'awaiting_result');
            });
    }

    private function closeOpenDisputes(Market $market, ?User $admin, string $resolution, ?string $note): void
    {
        Dispute::query()
            ->where('betting_market_id', $market->id)
            ->where('status', 'open')
            ->lockForUpdate()
            ->get()
            ->each(function (Dispute $open) use ($admin, $resolution, $note) {
                $open->status = 'resolved';
                $open->resolution = $resolution;
                $open->resolution_note = $note;
                $open->resolved_by = $admin?->id;
                $open->resolved_at = now();
                $open->save();
            });
    }
}
