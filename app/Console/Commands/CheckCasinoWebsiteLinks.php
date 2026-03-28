<?php

namespace App\Console\Commands;

use App\Models\Casino;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckCasinoWebsiteLinks extends Command
{
    protected $signature = 'casinos:check-links {--limit=100 : Max casinos to check}';

    protected $description = 'HEAD-request casino websites and flag broken links.';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        Casino::query()
            ->where('status', 'published')
            ->whereNotNull('website')
            ->orderBy('id')
            ->limit($limit)
            ->each(function (Casino $casino) {
                try {
                    $response = Http::timeout(10)->withoutRedirecting()->head($casino->website);
                    $ok = $response->successful() || in_array($response->status(), [301, 302, 303, 307, 308], true);
                    $casino->website_last_checked_at = now();
                    $casino->website_link_broken = ! $ok;
                    $casino->saveQuietly();
                } catch (\Throwable) {
                    $casino->website_last_checked_at = now();
                    $casino->website_link_broken = true;
                    $casino->saveQuietly();
                }
            });

        $this->info('Link check finished.');

        return self::SUCCESS;
    }
}
