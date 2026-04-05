<?php

namespace App\Jobs;

use App\Models\Casino;
use App\Models\EnrichmentQueue;
use App\Services\EnrichmentService;
use App\Services\NewsFetchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessEnrichmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function __construct(
        public EnrichmentQueue $enrichmentJob
    ) {}

    public function handle(EnrichmentService $enrichmentService, NewsFetchingService $newsService): void
    {
        $job = $this->enrichmentJob;
        $casino = $job->casino;

        $job->update([
            'status' => 'processing',
            'attempts' => $job->attempts + 1,
            'last_attempted_at' => now(),
        ]);

        $result = match ($job->job_type) {
            'logo' => $this->processLogo($casino, $enrichmentService),
            'screenshot' => $this->processScreenshot($casino, $enrichmentService),
            'content' => $this->processContent($casino, $enrichmentService),
            'news' => $this->processNews($casino, $newsService),
            default => throw new \InvalidArgumentException("Unknown job type: {$job->job_type}"),
        };

        $job->update([
            'status' => 'done',
            'result' => is_string($result) ? $result : json_encode($result),
        ]);

        $this->checkAndUpdateCasinoEnrichmentStatus($casino);
    }

    public function failed(?Throwable $exception): void
    {
        $this->enrichmentJob->update([
            'status' => 'failed',
            'result' => $exception ? $exception->getMessage() : 'Job failed after retries',
        ]);

        $this->checkAndUpdateCasinoEnrichmentStatus($this->enrichmentJob->casino);
    }

    private function checkAndUpdateCasinoEnrichmentStatus(Casino $casino): void
    {
        $pending = EnrichmentQueue::where('casino_id', $casino->id)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if (!$pending) {
            $hasFailed = EnrichmentQueue::where('casino_id', $casino->id)
                ->where('status', 'failed')
                ->exists();

            $casino->enrichment_status = $hasFailed ? 'failed' : 'done';
            $casino->save();
        }
    }

    private function processLogo(Casino $casino, EnrichmentService $service): string
    {
        $logoUrl = $service->fetchLogo($casino);
        if ($logoUrl) {
            $casino->update(['logo_url' => $logoUrl]);
            return "Logo fetched: {$logoUrl}";
        }
        return 'Logo not found';
    }

    private function processScreenshot(Casino $casino, EnrichmentService $service): string
    {
        return $service->captureScreenshotForCasino($casino);
    }

    private function processContent(Casino $casino, EnrichmentService $service): string
    {
        $description = $service->generateDescription($casino);
        if ($description) {
            $casino->update([
                'description' => $description,
                'short_description' => \Illuminate\Support\Str::limit($description, 200),
            ]);
            return 'Description generated';
        }
        return 'Description generation failed';
    }

    private function processNews(Casino $casino, NewsFetchingService $service): string
    {
        $count = $service->fetchNewsForCasino($casino);
        return "Fetched {$count} news items";
    }
}
