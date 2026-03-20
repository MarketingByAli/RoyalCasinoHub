<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\SeoSetting;
use App\Services\SeoService;

class HomeController extends Controller
{
    public function __invoke(SeoService $seoService)
    {
        $featuredCasinos = Casino::published()
            ->withCount('approvedReviews')
            ->orderByDesc('reviews_count')
            ->limit(12)
            ->get();

        $latestReviews = \App\Models\Review::approved()
            ->with(['casino', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        $countries = Casino::published()
            ->select('country', 'country_slug')
            ->distinct()
            ->orderBy('country')
            ->get();

        $metaTitle = SeoSetting::get('meta_title_default', 'RoyalCasinoHub — Trusted Online Casino Reviews & Ratings');
        $metaDescription = SeoSetting::get('meta_description_default', 'Discover trusted online casino reviews, ratings, and bonuses. Find the best casinos for your country.');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => SeoSetting::get('site_name', 'RoyalCasinoHub'),
            'url' => url('/'),
            'description' => $metaDescription,
        ];

        return view('home', [
            'featuredCasinos' => $featuredCasinos,
            'latestReviews' => $latestReviews,
            'countries' => $countries,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => url('/'),
            'schema' => $schema,
        ]);
    }
}
