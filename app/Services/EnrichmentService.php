<?php

namespace App\Services;

use App\Models\Casino;
use App\Models\EnrichmentQueue;
use Anthropic\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
            model: 'claude-haiku-4-5-20251001',
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

    /**
     * Fetches a website screenshot when possible, stores it on the public disk,
     * or applies the configured default image when there is no website or capture fails.
     */
    public function captureScreenshotForCasino(Casino $casino): string
    {
        $casino->refresh();

        $defaultUrl = config('casinos.default_screenshot_url');
        $defaultUrl = is_string($defaultUrl) && trim($defaultUrl) !== '' ? trim($defaultUrl) : null;

        if ($this->shouldSkipScreenshotFetch($casino)) {
            return 'Screenshot unchanged (already set manually or from a previous capture)';
        }

        if (! $this->normalizeHttpUrl($casino->website)) {
            if ($defaultUrl !== null) {
                $casino->update([
                    'screenshot_url' => $defaultUrl,
                    'screenshot_alt' => $casino->name,
                ]);

                return 'Applied default screenshot (no website URL)';
            }

            return 'No website; DEFAULT_CASINO_SCREENSHOT_URL not configured';
        }

        $storedUrl = $this->downloadWebsiteScreenshot($casino);
        if ($storedUrl !== null) {
            $casino->update([
                'screenshot_url' => $storedUrl,
                'screenshot_alt' => $casino->name.' website preview',
            ]);

            return 'Screenshot captured from website and stored';
        }

        if ($defaultUrl !== null) {
            $casino->update([
                'screenshot_url' => $defaultUrl,
                'screenshot_alt' => $casino->name,
            ]);

            return 'Screenshot capture failed; applied default image URL';
        }

        return 'Screenshot capture failed; no default image URL configured';
    }

    private function shouldSkipScreenshotFetch(Casino $casino): bool
    {
        $url = $casino->screenshot_url;
        if ($url === null || trim((string) $url) === '') {
            return false;
        }

        return ! str_contains(strtolower((string) $url), 'via.placeholder.com');
    }

    private function normalizeHttpUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }
        $trim = trim($url);
        if (! filter_var($trim, FILTER_VALIDATE_URL)) {
            return null;
        }
        $scheme = strtolower((string) parse_url($trim, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $trim : null;
    }

    private function downloadWebsiteScreenshot(Casino $casino): ?string
    {
        $driver = config('casinos.screenshot.driver', 'microlink');
        if ($driver !== 'microlink') {
            return null;
        }

        $website = $this->normalizeHttpUrl($casino->website);
        if ($website === null) {
            return null;
        }

        $request = Http::timeout(120)->connectTimeout(20)->acceptJson();
        $token = config('casinos.screenshot.microlink_api_key');
        if (is_string($token) && trim($token) !== '') {
            $request = $request->withToken(trim($token));
        }

        $response = $request->get('https://api.microlink.io', [
            'url' => $website,
            'screenshot' => 'true',
            'meta' => 'false',
        ]);

        if (! $response->successful()) {
            report(new \RuntimeException('Microlink screenshot failed: HTTP '.$response->status()));

            return null;
        }

        $screenshotUrl = data_get($response->json(), 'data.screenshot.url');
        if (! is_string($screenshotUrl) || ! filter_var($screenshotUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $image = Http::timeout(90)->connectTimeout(15)->get($screenshotUrl);
        if (! $image->successful()) {
            return null;
        }

        $body = $image->body();
        if (strlen($body) < 400) {
            return null;
        }

        $mimeType = strtolower((string) $image->header('Content-Type'));
        $ext = 'jpg';
        if (str_contains($mimeType, 'png')) {
            $ext = 'png';
        } elseif (str_contains($mimeType, 'webp')) {
            $ext = 'webp';
        } else {
            $apiType = strtolower((string) data_get($response->json(), 'data.screenshot.type', ''));
            if ($apiType === 'png') {
                $ext = 'png';
            } elseif ($apiType === 'webp') {
                $ext = 'webp';
            }
        }

        $disk = Storage::disk('public');
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $oldExt) {
            $oldPath = 'casino-screenshots/'.$casino->id.'.'.$oldExt;
            if ($disk->exists($oldPath)) {
                $disk->delete($oldPath);
            }
        }

        $path = 'casino-screenshots/'.$casino->id.'.'.$ext;
        Storage::disk('public')->put($path, $body);

        return Storage::disk('public')->url($path);
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
