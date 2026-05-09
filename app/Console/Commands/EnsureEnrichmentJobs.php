<?php

namespace App\Console\Commands;

use App\Models\Casino;
use App\Services\EnrichmentService;
use Illuminate\Console\Command;

class EnsureEnrichmentJobs extends Command
{
    protected $signature = 'enrichment:ensure-jobs';

    protected $description = 'Create missing enrichment_queue rows for every published casino (safe; does not reset existing job statuses).';

    public function handle(EnrichmentService $enrichmentService): int
    {
        $count = 0;
        Casino::query()->where('status', 'published')->each(function (Casino $casino) use ($enrichmentService, &$count) {
            $enrichmentService->createEnrichmentJobs($casino);
            $count++;
        });

        $this->info("Ensured enrichment job rows exist for {$count} published casinos. Run enrichment:process and queue:work to execute.");

        return self::SUCCESS;
    }
}
