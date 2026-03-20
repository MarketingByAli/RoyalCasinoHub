<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Services\SeoService;
use Illuminate\Http\Request;

class CasinoController extends Controller
{
    public function show(string $slug, SeoService $seoService)
    {
        $casino = Casino::published()
            ->with(['approvedReviews' => fn ($q) => $q->with('user')->latest()->limit(10)])
            ->with(['news' => fn ($q) => $q->latest('published_at')->limit(5)])
            ->where('slug', $slug)
            ->firstOrFail();

        $schema = $seoService->generateCasinoSchema($casino);
        $breadcrumbSchema = $seoService->generateBreadcrumbSchema([
            ['name' => 'Home', 'url' => url('/')],
            ['name' => $casino->country, 'url' => url("/country/{$casino->country_slug}")],
            ['name' => $casino->name, 'url' => null],
        ]);

        $metaTitle = $casino->meta_title ?: $seoService->getMetaTitle(null, $casino);
        $metaDescription = $casino->meta_description ?: $seoService->getMetaDescription(null, $casino);
        $canonical = $casino->canonical_url ?: url("/casino/{$casino->slug}");
        $robots = $casino->robots ?: 'index,follow';
        $ogImage = $casino->logo_url ?: null;

        return view('casino.show', [
            'casino' => $casino,
            'schema' => $schema,
            'breadcrumbSchema' => $breadcrumbSchema,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonical' => $canonical,
            'robots' => $robots,
            'ogImage' => $ogImage,
        ]);
    }
}
