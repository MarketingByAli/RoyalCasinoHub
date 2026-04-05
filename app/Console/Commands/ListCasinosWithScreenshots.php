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
                            {--diagnose : Print DB/queue/disk stats after a successful list}';

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
            if ($this->option('any')) {
                $this->warn('No casinos have screenshot_url set in the database (column is null or empty for everyone).');
            } else {
                $this->warn('No casinos matched (none with a stored capture URL containing "casino-screenshots").');
                $this->line('Tip: Run with --any to include default/manual URLs; auto-capture files live under storage/app/public/casino-screenshots/.');
            }
            $this->newLine();
            $this->diagnose();

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
            $this->line('Folder storage/app/public/casino-screenshots: not created yet — normal until the first screenshot job saves a file (Laravel creates this path automatically; no manual mkdir required).');
        } else {
            $n = count(glob($dir.DIRECTORY_SEPARATOR.'*') ?: []);
            $this->line("Folder casino-screenshots: {$n} file(s) on disk");
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

        $pendingAll = EnrichmentQueue::query()->where('status', 'pending')->count();
        $this->line("All enrichment_queue rows still pending (any job type): {$pendingAll}");

        if (Schema::hasTable('jobs')) {
            $pending = (int) DB::table('jobs')->count();
            $this->line("Laravel queue table `jobs` rows (waiting for worker): {$pending}");
        }

        $this->newLine();
        $this->warn('Your numbers mean work is backlogged: enrichment rows wait for `enrichment:process` to dispatch them; `jobs` waits for `queue:work`.');
        $this->line('1) Start a worker and keep it running (Supervisor/cPanel daemon): php artisan queue:work --sleep=3 --tries=3');
        $this->line('2) Dispatch more batches (scheduler only does 20 every 5 min): php artisan enrichment:process --limit=100');
        $this->line('3) Run: php artisan storage:link   (once) so /storage URLs work after files are saved.');
    }
}

