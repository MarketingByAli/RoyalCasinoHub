<?php

namespace App\Betting\Services;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\Market;
use App\Betting\Models\MarketParticipant;
use App\Models\User;

class BettingStatsService
{
    /**
     * @return array{
     *   completed_bets: int,
     *   voided_bets: int,
     *   wins: int,
     *   losses: int,
     *   net_points: float,
     *   dispute_count: int,
     *   dispute_rate: float,
     *   account_age_days: int
     * }
     */
    public function forUser(User $user): array
    {
        $participantMarketIds = MarketParticipant::where('user_id', $user->id)->pluck('betting_market_id');

        $completed = Market::whereIn('id', $participantMarketIds)
            ->where('status', MarketStatus::Settled)
            ->count();

        $voided = Market::whereIn('id', $participantMarketIds)
            ->where('status', MarketStatus::Voided)
            ->count();

        $wins = 0;
        $losses = 0;

        Market::whereIn('id', $participantMarketIds)
            ->where('status', MarketStatus::Settled)
            ->with('participants')
            ->each(function (Market $market) use ($user, &$wins, &$losses) {
                $participant = $market->participants->firstWhere('user_id', $user->id);
                if (! $participant || ! $market->winning_outcome) {
                    return;
                }
                if ($participant->outcome === $market->winning_outcome) {
                    $wins++;
                } else {
                    $losses++;
                }
            });

        $disputeCount = $user->bettingDisputes()->count();
        $totalResolved = max(1, $completed + $voided);

        $wallet = app(PlayWalletService::class)->getOrCreateWallet($user);
        $netFromLedger = $wallet->available + $wallet->locked - (float) config('betting.starter_points', 10000);

        return [
            'completed_bets' => $completed,
            'voided_bets' => $voided,
            'wins' => $wins,
            'losses' => $losses,
            'net_points' => round($netFromLedger, 2),
            'dispute_count' => $disputeCount,
            'dispute_rate' => round($disputeCount / $totalResolved, 4),
            'account_age_days' => $user->created_at?->diffInDays(now()) ?? 0,
        ];
    }
}
