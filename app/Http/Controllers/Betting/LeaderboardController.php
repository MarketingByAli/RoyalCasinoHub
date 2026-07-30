<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Services\LeaderboardService;
use App\Http\Controllers\Controller;

class LeaderboardController extends Controller
{
    public function weekly(LeaderboardService $leaderboard)
    {
        $rows = $leaderboard->currentWeekly(50);

        if ($rows->isEmpty()) {
            $leaderboard->snapshotWeekly();
            $rows = $leaderboard->currentWeekly(50);
        }

        return view('betting.leaderboard.weekly', compact('rows'));
    }
}
