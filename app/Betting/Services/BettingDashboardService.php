<?php

namespace App\Betting\Services;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\Dispute;
use App\Betting\Models\Market;
use App\Betting\Models\UserReport;
use App\Betting\Models\Wallet;
use App\Models\User;

class BettingDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function kpis(): array
    {
        $statusCounts = Market::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stuck = Market::query()
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status', MarketStatus::Open)
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<', now());
                })->orWhere(function ($q2) {
                    $q2->where('status', MarketStatus::DisputeWindow)
                        ->whereNotNull('dispute_window_ends_at')
                        ->where('dispute_window_ends_at', '<', now());
                });
            })
            ->count();

        return [
            'pending_review' => (int) ($statusCounts[MarketStatus::PendingReview->value] ?? 0),
            'open' => (int) ($statusCounts[MarketStatus::Open->value] ?? 0),
            'partially_matched' => (int) ($statusCounts[MarketStatus::PartiallyMatched->value] ?? 0),
            'fully_matched' => (int) ($statusCounts[MarketStatus::FullyMatched->value] ?? 0),
            'under_dispute' => (int) ($statusCounts[MarketStatus::UnderDispute->value] ?? 0),
            'open_disputes' => Dispute::where('status', 'open')->count(),
            'open_reports' => UserReport::where('status', 'open')->count(),
            'stuck_markets' => $stuck,
            'total_available' => (float) Wallet::sum('available'),
            'total_locked' => (float) Wallet::sum('locked'),
            'active_wallets' => Wallet::where(function ($q) {
                $q->where('available', '>', 0)->orWhere('locked', '>', 0);
            })->count(),
        ];
    }

    public function searchUsers(string $query, int $limit = 20)
    {
        $like = '%'.$query.'%';

        return User::query()
            ->with('bettingProfile')
            ->where(function ($q) use ($like) {
                $q->where('email', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhereHas('bettingProfile', fn ($p) => $p->where('username', 'like', $like));
            })
            ->limit($limit)
            ->get();
    }
}
