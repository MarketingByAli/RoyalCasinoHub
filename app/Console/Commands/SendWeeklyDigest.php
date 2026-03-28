<?php

namespace App\Console\Commands;

use App\Models\Review;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    protected $signature = 'digest:weekly';

    protected $description = 'Send weekly email digest to users who opted in (saved casinos activity).';

    public function handle(): int
    {
        $since = now()->subDays(7);

        User::query()->orderBy('id')->chunkById(100, function ($users) use ($since) {
            foreach ($users as $user) {
                if (! ($user->settings['digest_weekly'] ?? false)) {
                    continue;
                }

                $favoriteIds = $user->favoriteCasinos()->pluck('id');
                if ($favoriteIds->isEmpty()) {
                    continue;
                }

                $count = Review::query()
                    ->whereIn('casino_id', $favoriteIds)
                    ->where('status', 'approved')
                    ->where('created_at', '>=', $since)
                    ->count();

                if ($count === 0) {
                    continue;
                }

                Mail::raw(
                    "You have {$count} new approved review(s) in the past week on casinos you saved. Visit ".config('app.url').'/account',
                    function ($message) use ($user) {
                        $message->to($user->email)->subject('Your RoyalCasinoHub weekly digest');
                    }
                );
            }
        });

        $this->info('Digest run complete.');

        return self::SUCCESS;
    }
}
