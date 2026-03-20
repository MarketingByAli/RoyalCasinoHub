<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEnrichmentJob;
use App\Models\EnrichmentQueue;
use Illuminate\Console\Command;

class ProcessEnrichmentQueue extends Command
{
    protected $signature = 'enrichment:process {--limit=10 : Number of jobs to process}';

    protected $description = 'Process pending enrichment queue jobs (Action Scheduler pattern)';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $jobs = EnrichmentQueue::pending()
            ->with('casino')
            ->limit($limit)
            ->get();

        foreach ($jobs as $job) {
            ProcessEnrichmentJob::dispatch($job);
        }

        $this->info("Dispatched {$jobs->count()} enrichment jobs.");
        return self::SUCCESS;
    }
}
