<?php

namespace App\Console\Commands;

use App\Models\Casino;
use App\Services\NewsFetchingService;
use Illuminate\Console\Command;

class RefreshCasinoNews extends Command
{
    protected $signature = 'news:refresh {--limit=50 : Number of casinos to process}';

    protected $description = 'Refresh news for casinos that have not been updated in 24 hours';

    public function handle(NewsFetchingService $newsService): int
    {
        $limit = (int) $this->option('limit');

        $casinos = Casino::published()
            ->where(function ($q) {
                $q->whereNull('news_last_fetched_at')
                    ->orWhere('news_last_fetched_at', '<', now()->subDay());
            })
            ->limit($limit)
            ->get();

        $count = 0;
        $failed = 0;
        foreach ($casinos as $casino) {
            try {
                $newsService->fetchNewsForCasino($casino);
                $count++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Failed to fetch news for {$casino->name}: {$e->getMessage()}");
                report($e);
            }
        }

        $this->info("Refreshed news for {$count} casinos. {$failed} failed.");
        return $failed > 0 && $count === 0 ? self::FAILURE : self::SUCCESS;
    }
}
