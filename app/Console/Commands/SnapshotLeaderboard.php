<?php

namespace App\Console\Commands;

use App\Betting\Services\LeaderboardService;
use Illuminate\Console\Command;

class SnapshotLeaderboard extends Command
{
    protected $signature = 'betting:snapshot-leaderboard';

    protected $description = 'Snapshot weekly betting leaderboard rankings';

    public function handle(LeaderboardService $leaderboard): int
    {
        $rows = $leaderboard->snapshotWeekly();
        $this->info('Leaderboard snapshot created with '.$rows->count().' rows.');

        return self::SUCCESS;
    }
}
