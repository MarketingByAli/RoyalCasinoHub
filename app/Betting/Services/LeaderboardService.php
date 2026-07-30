<?php

namespace App\Betting\Services;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\LeaderboardSnapshot;
use App\Betting\Models\Market;
use App\Betting\Models\MarketParticipant;
use App\Betting\Models\UserProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public function snapshotWeekly(?\DateTimeInterface $anchor = null): Collection
    {
        $anchor = \Carbon\Carbon::parse($anchor ?? now())->startOfWeek();
        $end = $anchor->copy()->endOfWeek();

        LeaderboardSnapshot::where('period', 'weekly')
            ->where('period_start', $anchor->toDateString())
            ->delete();

        $rows = MarketParticipant::query()
            ->select([
                'betting_market_participants.user_id',
                DB::raw('COUNT(*) as settled_markets'),
                DB::raw("SUM(CASE WHEN betting_markets.winning_outcome = betting_market_participants.outcome THEN 1 ELSE 0 END) as wins"),
                DB::raw("SUM(CASE WHEN betting_markets.winning_outcome != betting_market_participants.outcome THEN 1 ELSE 0 END) as losses"),
                DB::raw("SUM(CASE WHEN betting_markets.winning_outcome = betting_market_participants.outcome THEN betting_market_participants.stake_amount ELSE -betting_market_participants.stake_amount END) as net_points"),
            ])
            ->join('betting_markets', 'betting_markets.id', '=', 'betting_market_participants.betting_market_id')
            ->where('betting_market_participants.status', 'active')
            ->where('betting_markets.status', MarketStatus::Settled)
            ->whereBetween('betting_markets.updated_at', [$anchor, $end])
            ->groupBy('betting_market_participants.user_id')
            ->orderByDesc('net_points')
            ->get();

        $hidden = UserProfile::where('hide_betting_activity', true)->pluck('user_id')->all();
        $rank = 0;
        $snapshots = collect();

        foreach ($rows as $row) {
            if (in_array($row->user_id, $hidden, true)) {
                continue;
            }

            $rank++;
            $snapshots->push(LeaderboardSnapshot::create([
                'period' => 'weekly',
                'period_start' => $anchor->toDateString(),
                'period_end' => $end->toDateString(),
                'user_id' => $row->user_id,
                'rank' => $rank,
                'wins' => (int) $row->wins,
                'losses' => (int) $row->losses,
                'net_points' => (float) $row->net_points,
                'settled_markets' => (int) $row->settled_markets,
            ]));
        }

        return $snapshots;
    }

    public function currentWeekly(int $limit = 50)
    {
        $start = now()->startOfWeek()->toDateString();

        return LeaderboardSnapshot::query()
            ->with(['user.bettingProfile'])
            ->where('period', 'weekly')
            ->where('period_start', $start)
            ->orderBy('rank')
            ->limit($limit)
            ->get();
    }
}
