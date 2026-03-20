<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\SeoSetting;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $casinos = Casino::published()
            ->byCountry($slug)
            ->withCount('approvedReviews')
            ->orderByDesc('reviews_count')
            ->paginate(24);

        $country = $casinos->first()?->country ?? str_replace('-', ' ', ucwords($slug));

        $metaTitle = "Best Online Casinos in {$country} " . now()->year . " | " . SeoSetting::get('site_name', 'RoyalCasinoHub');
        $metaDescription = "Discover the best online casinos in {$country}. Honest reviews, ratings, bonuses and more. Updated " . now()->year . ".";

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $country, 'item' => null],
            ],
        ];

        $canonical = url("/country/{$slug}");

        $prevPage = $casinos->currentPage() > 1
            ? url("/country/{$slug}") . ($casinos->currentPage() > 2 ? '?page=' . ($casinos->currentPage() - 1) : '')
            : null;
        $nextPage = $casinos->hasMorePages()
            ? url("/country/{$slug}?page=" . ($casinos->currentPage() + 1))
            : null;

        return view('country.show', [
            'casinos' => $casinos,
            'country' => $country,
            'countrySlug' => $slug,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => $canonical,
            'breadcrumbSchema' => $breadcrumbSchema,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
        ]);
    }
}
