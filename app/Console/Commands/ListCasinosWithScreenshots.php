<?php

namespace App\Console\Commands;

use App\Models\Casino;
use App\Models\EnrichmentQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ListCasinosWithScreenshots extends Command
{
    protected $signature = 'casinos:list-screenshots
                            {--limit=20 : Number of casinos to show}
                            {--any : Include any non-empty screenshot_url (not only locally stored captures)}
                            {--diagnose : Print DB/queue/disk stats (also runs when default list is empty)}';

    protected $description = 'List recent casinos with screenshots (default: auto-captured files under storage/casino-screenshots)';

    public function handle(): int
    {
        $limit = max(1, min(100, (int) $this->option('limit')));

        $query = Casino::query()
            ->whereNotNull('screenshot_url')
            ->where('screenshot_url', '!=', '');

        if (! $this->option('any')) {
            $query->where('screenshot_url', 'like', '%casino-screenshots%');
        }

        $casinos = $query
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'screenshot_url', 'website', 'updated_at']);

        if ($casinos->isEmpty()) {
            $this->warn('No casinos matched. Try --any to list any listing with a screenshot URL set.');
            if (! $this->option('any')) {
                $this->line('Tip: Auto-capture saves files under storage/app/public/casino-screenshots/ and sets a URL containing that path.');
            }
            if (! $this->option('any') || $this->option('diagnose')) {
                $this->newLine();
                $this->diagnose();
            }

            return self::SUCCESS;
        }

        if ($this->option('diagnose')) {
            $this->newLine();
            $this->diagnose();
            $this->newLine();
        }

        $rows = $casinos->map(fn (Casino $c) => [
            'id' => $c->id,
            'name' => Str::limit($c->name, 36),
            'slug' => $c->slug,
            'listing' => url('/casino/'.$c->slug),
            'screenshot' => Str::limit($c->screenshot_url, 72),
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

    private function diagnose(): void
    {
        $this->info('── Screenshot diagnostics ──');

        $withAny = Casino::query()
            ->whereNotNull('screenshot_url')
            ->where('screenshot_url', '!=', '')
            ->count();
        $this->line("Casinos with any screenshot_url: {$withAny}");

        $storedPattern = Casino::query()
            ->whereNotNull('screenshot_url')
            ->where('screenshot_url', 'like', '%casino-screenshots%')
            ->count();
        $this->line("…of those, URL contains \"casino-screenshots\" (stored capture): {$storedPattern}");

        $samples = Casino::query()
            ->whereNotNull('screenshot_url')
            ->where('screenshot_url', '!=', '')
            ->orderByDesc('id')
            ->limit(5)
            ->pluck('screenshot_url');
        if ($samples->isEmpty()) {
            $this->line('Sample screenshot_url values: (none)');
        } else {
            $this->line('Latest screenshot_url samples:');
            foreach ($samples as $u) {
                $this->line('  • '.Str::limit($u, 120));
            }
        }

        $dir = storage_path('app/public/casino-screenshots');
        if (! is_dir($dir)) {
            $this->line('Disk folder storage/app/public/casino-screenshots: missing (create by saving a capture, or mkdir + chmod)');
        } else {
            $n = count(glob($dir.DIRECTORY_SEPARATOR.'*') ?: []);
            $this->line("Disk folder casino-screenshots: {$n} file(s)");
        }

        $byStatus = EnrichmentQueue::query()
            ->where('job_type', 'screenshot')
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');
        if ($byStatus->isEmpty()) {
            $this->line('Enrichment rows for job_type=screenshot: none (no casinos queued for screenshot yet)');
        } else {
            $this->line('Enrichment screenshot jobs: '.$byStatus->map(fn ($c, $s) => "{$s}={$c}")->implode(', '));
        }

        if (Schema::hasTable('jobs')) {
            $pending = (int) DB::table('jobs')->count();
            $this->line("Laravel queue table `jobs` rows (waiting for worker): {$pending}");
        }

        $this->newLine();
        $this->line('Typical fix if stored count is 0: run a queue worker continuously, e.g. php artisan queue:work');
        $this->line('Then: php artisan enrichment:process   (or wait for cron schedule:run every minute)');
        $this->line('Re-queue a casino from admin (Queue enrichment) after deploy if jobs failed earlier.');
    }
}

