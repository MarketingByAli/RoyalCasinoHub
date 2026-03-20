<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    private const CHUNK_SIZE = 1000;

    public function index(): Response
    {
        $content = Cache::remember('sitemap_index', 3600, function () {
            $baseUrl = config('app.url');
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            $lastmod = now()->toW3cString();
            $xml .= '<sitemap><loc>' . htmlspecialchars($baseUrl) . '/sitemap-static.xml</loc><lastmod>' . $lastmod . '</lastmod></sitemap>';
            $xml .= '<sitemap><loc>' . htmlspecialchars($baseUrl) . '/sitemap-countries.xml</loc><lastmod>' . $lastmod . '</lastmod></sitemap>';

            $casinoCount = Casino::published()->count();
            $chunks = (int) ceil($casinoCount / self::CHUNK_SIZE);
            for ($i = 0; $i < $chunks; $i++) {
                $xml .= '<sitemap><loc>' . htmlspecialchars($baseUrl) . '/sitemap-casinos-' . ($i + 1) . '.xml</loc><lastmod>' . $lastmod . '</lastmod></sitemap>';
            }

            $xml .= '</sitemapindex>';
            return $xml;
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function static(): Response
    {
        $content = Cache::remember('sitemap_static', 3600, function () {
            $baseUrl = config('app.url');
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            $urls = [
                ['url' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
                ['url' => $baseUrl . '/reviews', 'priority' => '0.9', 'changefreq' => 'daily'],
            ];

            foreach ($urls as $item) {
                $xml .= '<url>';
                $xml .= '<loc>' . htmlspecialchars($item['url']) . '</loc>';
                $xml .= '<lastmod>' . now()->toW3cString() . '</lastmod>';
                $xml .= '<changefreq>' . $item['changefreq'] . '</changefreq>';
                $xml .= '<priority>' . $item['priority'] . '</priority>';
                $xml .= '</url>';
            }

            $xml .= '</urlset>';
            return $xml;
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function countries(): Response
    {
        $content = Cache::remember('sitemap_countries', 3600, function () {
            $baseUrl = config('app.url');
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            $countries = Casino::published()
                ->select('country_slug')
                ->distinct()
                ->orderBy('country_slug')
                ->cursor();

            foreach ($countries as $casino) {
                $slug = $casino->country_slug;
                $xml .= '<url>';
                $xml .= '<loc>' . htmlspecialchars($baseUrl . '/country/' . $slug) . '</loc>';
                $xml .= '<lastmod>' . now()->toW3cString() . '</lastmod>';
                $xml .= '<changefreq>weekly</changefreq>';
                $xml .= '<priority>0.8</priority>';
                $xml .= '</url>';
            }

            $xml .= '</urlset>';
            return $xml;
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function casinos(int $page): Response
    {
        if ($page < 1) {
            abort(404);
        }
        $maxPage = max(1, (int) ceil(Casino::published()->count() / self::CHUNK_SIZE));
        if ($page > $maxPage) {
            abort(404);
        }

        $content = Cache::remember("sitemap_casinos_{$page}", 3600, function () use ($page) {
            $baseUrl = config('app.url');
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            Casino::published()
                ->select('slug', 'updated_at')
                ->orderBy('id')
                ->offset(($page - 1) * self::CHUNK_SIZE)
                ->limit(self::CHUNK_SIZE)
                ->cursor()
                ->each(function ($casino) use (&$xml, $baseUrl) {
                    $xml .= '<url>';
                    $xml .= '<loc>' . htmlspecialchars($baseUrl . '/casino/' . $casino->slug) . '</loc>';
                    $xml .= '<lastmod>' . $casino->updated_at->toW3cString() . '</lastmod>';
                    $xml .= '<changefreq>weekly</changefreq>';
                    $xml .= '<priority>0.7</priority>';
                    $xml .= '</url>';
                });

            $xml .= '</urlset>';
            return $xml;
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
