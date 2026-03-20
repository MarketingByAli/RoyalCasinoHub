<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = substr($request->get('q', ''), 0, 255);
        $casinos = null;

        if (strlen($query) >= 2) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);
            $casinos = Casino::published()
                ->where(function ($q) use ($escaped) {
                    $q->where('name', 'like', "%{$escaped}%")
                        ->orWhere('description', 'like', "%{$escaped}%")
                        ->orWhere('short_description', 'like', "%{$escaped}%");
                })
                ->withCount('approvedReviews')
                ->orderByDesc('reviews_count')
                ->paginate(24)
                ->withQueryString();
        }

        $metaTitle = $query ? "Search: {$query} | RoyalCasinoHub" : "Search Casinos | RoyalCasinoHub";
        $metaDescription = $query ? "Search results for {$query}." : "Search our directory of online casinos.";

        $canonical = $query
            ? url("/search?q=" . urlencode($query))
            : url('/search');

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $query ? "Search: {$query}" : 'Search', 'item' => null],
            ],
        ];

        $prevPage = null;
        $nextPage = null;
        if ($casinos instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $prevPage = $casinos->currentPage() > 1
                ? url('/search') . '?q=' . urlencode($query) . ($casinos->currentPage() > 2 ? '&page=' . ($casinos->currentPage() - 1) : '')
                : null;
            $nextPage = $casinos->hasMorePages()
                ? url('/search') . '?q=' . urlencode($query) . '&page=' . ($casinos->currentPage() + 1)
                : null;
        }

        return view('search.index', [
            'casinos' => $casinos ?? collect(),
            'query' => $query,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => $canonical,
            'breadcrumbSchema' => $breadcrumbSchema,
            'noindex' => true,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
        ]);
    }
}
