<?php

namespace App\Console\Commands;

use App\Models\Casino;
use Illuminate\Console\Command;

class ListCasinosWithScreenshots extends Command
{
    protected $signature = 'casinos:list-screenshots
                            {--limit=20 : Number of casinos to show}
                            {--any : Include any non-empty screenshot_url (not only locally stored captures)}';

    protected $description = 'List recent casinos with screenshots (default: auto-captured files under storage/casino-screenshots)';

    public function handle(): int
    {
        $limit = max(1, min(100, (int) $this->option('limit')));

        $query = Casino::query()
            ->whereNotNull('screenshot_url')
            ->where('screenshot_url', '!=', '');

        if (! $this->option('any')) {
            $query->where(function ($q) {
                $q->where('screenshot_url', 'like', '%/casino-screenshots/%')
                    ->orWhere('screenshot_url', 'like', '%/storage/casino-screenshots/%');
            });
        }

        $casinos = $query
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'screenshot_url', 'website', 'updated_at']);

        if ($casinos->isEmpty()) {
            $this->warn('No casinos matched. Try --any to list any listing with a screenshot URL set.');
            if (! $this->option('any')) {
                $this->line('Tip: Captures from Microlink are saved under /storage/casino-screenshots/ on your app URL.');
            }

            return self::SUCCESS;
        }

        $rows = $casinos->map(fn (Casino $c) => [
            'id' => $c->id,
            'name' => \Illuminate\Support\Str::limit($c->name, 36),
            'slug' => $c->slug,
            'listing' => url('/casino/'.$c->slug),
            'screenshot' => \Illuminate\Support\Str::limit($c->screenshot_url, 72),
            'updated' => $c->updated_at?->toDateTimeString() ?? '',
        ]);

        $this->table(
            ['ID', 'Name', 'Slug', 'Listing URL', 'Screenshot URL', 'Updated'],
            $rows->map(fn ($r) => [
                $r['id'],
                $r['name'],
                $r['slug'],
                $r['listing'],
                $r['screenshot'],
                $r['updated'],
            ])->all()
        );

        $this->info('Listed '.$casinos->count().' casino(s). Open listing URLs in a browser to review the page + image.');

        return self::SUCCESS;
    }
}
