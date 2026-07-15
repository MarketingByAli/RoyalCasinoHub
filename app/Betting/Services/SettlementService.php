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
        $event->winning_outcome = $winningOutcome;
        $event->result_published_at = now();
        $event->status = 'completed';
        $event->save();

        Market::query()
            ->where('betting_event_id', $event->id)
            ->whereIn('status', [
                MarketStatus::FullyMatched,
                MarketStatus::Locked,
                MarketStatus::InProgress,
                MarketStatus::PendingResult,
            ])
            ->each(function (Market $market) use ($winningOutcome, $admin) {
                $this->publishMarketResult($market, $winningOutcome, $admin);
            });

        return $event->fresh();
    }

    public function publishMarketResult(Market $market, string $winningOutcome, ?User $admin = null): Market
    {
        if (! $market->isMatched()) {
            throw new RuntimeException('Market must be matched before publishing result.');
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
    }

    public function finalizeAfterDisputeWindow(Market $market): Market
    {
        if ($market->status !== MarketStatus::DisputeWindow) {
            throw new RuntimeException('Market is not in dispute window.');
        }

        if ($market->dispute_window_ends_at && $market->dispute_window_ends_at->isFuture()) {
            throw new RuntimeException('Dispute window still open.');
        }

        if ($market->disputes()->where('status', 'open')->exists()) {
            return $this->stateMachine->transition($market, MarketStatus::UnderDispute, null, 'open_dispute_exists');
        }

        return $this->settleMarket($market);
    }

    public function settleMarket(Market $market): Market
    {
        if (! in_array($market->status, [MarketStatus::DisputeWindow, MarketStatus::UnderDispute], true)) {
            throw new RuntimeException('Market cannot be settled from current state.');
        }

        return DB::transaction(function () use ($market) {
            $market = Market::where('id', $market->id)->lockForUpdate()->firstOrFail();

            if ($market->status === MarketStatus::Settled) {
                return $market;
            }

            $winningOutcome = $market->winning_outcome;
            if (! $winningOutcome) {
                throw new RuntimeException('No winning outcome set.');
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

            foreach ($market->participants()->with('user')->get() as $participant) {
                if ($this->walletService->openLiability($participant->user) >= $stake) {
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
        if ($market->status !== MarketStatus::DisputeWindow) {
            throw new RuntimeException('Disputes can only be opened during the dispute window.');
        }

        if (! $market->participants()->where('user_id', $user->id)->exists()) {
            throw new RuntimeException('Only participants can dispute.');
        }

        $dispute = Dispute::create([
            'betting_market_id' => $market->id,
            'user_id' => $user->id,
            'reason_category' => $reasonCategory,
            'explanation' => $explanation,
            'status' => 'open',
        ]);

        $this->stateMachine->transition($market, MarketStatus::UnderDispute, $user, 'dispute_opened');

        return $dispute;
    }

    public function resolveDispute(Dispute $dispute, User $admin, string $resolution, ?string $note): Market
    {
        $dispute->status = 'resolved';
        $dispute->resolution = $resolution;
        $dispute->resolution_note = $note;
        $dispute->resolved_by = $admin->id;
        $dispute->resolved_at = now();
        $dispute->save();

        $market = $dispute->market;

        return match ($resolution) {
            'confirm' => $this->settleMarket($market),
            'void' => $this->voidMarket($market, $admin, 'dispute_void'),
            default => throw new RuntimeException('Unknown resolution.'),
        };
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
}
