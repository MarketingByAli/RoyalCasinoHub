<?php

namespace App\Services;

use App\Models\Casino;
use App\Models\EnrichmentQueue;
use Anthropic\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EnrichmentService
{
    private const LOGO_MAX_RETRIES = 3;

    private const LOGO_RETRY_DELAY_MS = 1000;

    private const CLAUDE_MAX_RETRIES = 3;

    private const CLAUDE_RETRY_DELAY_MS = 2000;

    public function fetchLogo(Casino $casino): ?string
    {
        if (!$casino->website) {
            return null;
        }

        $domain = parse_url($casino->website, PHP_URL_HOST);
        if (!$domain) {
            return null;
        }

        $lastException = null;
        for ($attempt = 1; $attempt <= self::LOGO_MAX_RETRIES; $attempt++) {
            try {
                $logoUrl = $this->attemptLogoFetch($domain);
                if ($logoUrl !== null) {
                    return $logoUrl;
                }
            } catch (\Throwable $e) {
                $lastException = $e;
                report($e);
                if ($attempt < self::LOGO_MAX_RETRIES) {
                    usleep(self::LOGO_RETRY_DELAY_MS * 1000 * $attempt);
                }
            }
        }

        if ($lastException) {
            report($lastException);
        }
        return null;
    }

    private function attemptLogoFetch(string $domain): ?string
    {
        $clearbitUrl = "https://logo.clearbit.com/{$domain}";
        $response = Http::timeout(8)->connectTimeout(5)->head($clearbitUrl);
        if ($response->successful()) {
            return $clearbitUrl;
        }

        $faviconUrl = "https://www.google.com/s2/favicons?domain={$domain}&sz=128";
        $faviconResponse = Http::timeout(8)->connectTimeout(5)->get($faviconUrl);
        $contentType = $faviconResponse->header('Content-Type');
        if ($faviconResponse->successful() && $contentType && str_starts_with($contentType, 'image/')) {
            return $faviconUrl;
        }

        return null;
    }

    public function generateDescription(Casino $casino): ?string
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            return null;
        }

        $lastException = null;
        for ($attempt = 1; $attempt <= self::CLAUDE_MAX_RETRIES; $attempt++) {
            try {
                $description = $this->attemptDescriptionGeneration($casino, $apiKey);
                if ($description !== null) {
                    return $description;
                }
            } catch (\Throwable $e) {
                $lastException = $e;
                report($e);
                if (!$this->isRetryableClaudeError($e)) {
                    return null;
                }
                if ($attempt < self::CLAUDE_MAX_RETRIES) {
                    $delayMs = self::CLAUDE_RETRY_DELAY_MS * pow(2, $attempt - 1);
                    usleep($delayMs * 1000);
                } else {
                    throw $e;
                }
            }
        }

        return null;
    }

    private function isRetryableClaudeError(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());
        return str_contains($message, 'rate limit')
            || str_contains($message, '429')
            || str_contains($message, 'timeout')
            || str_contains($message, 'connection')
            || str_contains($message, 'overloaded');
    }

    private function attemptDescriptionGeneration(Casino $casino, string $apiKey): ?string
    {
        $client = new Client(apiKey: $apiKey);
        $message = $client->messages->create(
            maxTokens: 500,
            model: 'claude-sonnet-4-20250514',
            messages: [
                [
                    'role' => 'user',
                    'content' => "Write a professional 200-word description for an online casino review. Casino name: {$casino->name}. Country: {$casino->country}. Website: " . ($casino->website ?? 'Not specified') . ". Write in third person, be factual and informative. Focus on what players would want to know. Do not include promotional language or guarantees.",
                ],
            ],
        );

        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }
        return Str::limit(trim($text), 2000) ?: null;
    }

    public function getScreenshotPlaceholder(Casino $casino): string
    {
        return "https://via.placeholder.com/1200x630/0f0f1a/D4AF37?text=" . urlencode($casino->name);
    }

    public function createEnrichmentJobs(Casino $casino): void
    {
        $jobs = ['logo', 'screenshot', 'content', 'news'];
        foreach ($jobs as $jobType) {
            EnrichmentQueue::firstOrCreate(
                [
                    'casino_id' => $casino->id,
                    'job_type' => $jobType,
                ],
                ['status' => 'pending']
            );
        }
    }
}
