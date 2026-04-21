<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\SeoSetting;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Casino::published()
            ->selectRaw('country, country_slug, COUNT(*) as casinos_count')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country', 'country_slug')
            ->orderBy('country')
            ->get();

        $metaTitle = 'Browse Casinos by Country | '.SeoSetting::get('site_name', 'RoyalCasinoHub');
        $metaDescription = 'Explore online casinos available in '.$countries->count().'+ countries. Find the best casinos for your region with expert reviews and ratings.';

        return view('country.index', [
            'countries' => $countries,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => route('countries.index'),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $sort = $request->query('sort', 'name');
        if (! in_array($sort, ['name', 'top-rated', 'most-reviewed', 'newest'], true)) {
            $sort = 'name';
        }

        $query = Casino::published()
            ->byCountry($slug)
            ->withCount('approvedReviews');

        match ($sort) {
            'top-rated' => $query->orderByDesc('average_rating')->orderBy('name'),
            'most-reviewed' => $query->orderByDesc('reviews_count')->orderBy('name'),
            'newest' => $query->latest(),
            default => $query->orderBy('region')->orderBy('locality')->orderBy('name'),
        };

        $casinos = $query->paginate(24)->withQueryString();

        $country = $casinos->first()?->country ?? str_replace('-', ' ', ucwords($slug));

        $metaTitle = "Best Online Casinos in {$country} ".now()->year.' | '.SeoSetting::get('site_name', 'RoyalCasinoHub');
        $metaDescription = "Discover the best online casinos in {$country}. Honest reviews, ratings, bonuses and more. Updated ".now()->year.'.';

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $country, 'item' => null],
            ],
        ];

        $canonical = $casinos->url($casinos->currentPage());

        $prevPage = $casinos->currentPage() > 1 ? $casinos->previousPageUrl() : null;
        $nextPage = $casinos->hasMorePages() ? $casinos->nextPageUrl() : null;

        return view('country.show', [
            'casinos' => $casinos,
            'country' => $country,
            'countrySlug' => $slug,
            'sort' => $sort,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => $canonical,
            'breadcrumbSchema' => $breadcrumbSchema,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
        ]);
    }
}
