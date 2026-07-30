<?php

namespace App\Services;

use App\Models\Casino;
use App\Models\CasinoNews;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsFetchingService
{
    private const MAX_RETRIES = 3;

    private const RETRY_DELAY_MS = 1500;

    public function fetchNewsForCasino(Casino $casino): int
    {
        $query = urlencode($casino->name . ' casino');
        $rssUrl = "https://news.google.com/rss/search?q={$query}&hl=en-US&gl=US&ceid=US:en";

        $lastException = null;
        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $count = $this->attemptNewsFetch($casino, $rssUrl);
                $casino->update(['news_last_fetched_at' => now()]);
                return $count;
            } catch (\Throwable $e) {
                $lastException = $e;
                report($e);
                if ($attempt < self::MAX_RETRIES) {
                    usleep(self::RETRY_DELAY_MS * 1000 * $attempt);
                } else {
                    throw $e;
                }
            }
        }

        throw $lastException ?? new \RuntimeException('News fetch failed');
    }

    private function attemptNewsFetch(Casino $casino, string $rssUrl): int
    {
        $response = Http::timeout(15)->connectTimeout(8)->get($rssUrl);
        if (!$response->successful()) {
            throw new \RuntimeException("Google News RSS returned status {$response->status()}");
        }

        $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        if ($xml === false) {
            throw new \RuntimeException('Invalid RSS XML response');
        }

        $count = 0;
        $items = $xml->channel->item ?? [];
        $maxItems = 5;

        foreach ($items as $item) {
            if ($count >= $maxItems) {
                break;
            }

            $title = (string) ($item->title ?? '');
            $link = (string) ($item->link ?? '');
            $pubDate = isset($item->pubDate) ? strtotime((string) $item->pubDate) : null;

            if (empty($link)) {
                continue;
            }

            CasinoNews::updateOrCreate(
                [
                    'casino_id' => $casino->id,
                    'url' => Str::limit($link, 2048),
                ],
                [
                    'title' => Str::limit($title, 255),
                    'source' => 'Google News',
                    'published_at' => $pubDate ? date('Y-m-d H:i:s', $pubDate) : null,
                ]
            );
            $count++;
        }

        return $count;
    }
}
