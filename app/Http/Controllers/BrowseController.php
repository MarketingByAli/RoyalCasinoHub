<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\SeoSetting;
use App\Models\Tag;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    public function index()
    {
        $tags = Tag::query()
            ->withCount(['casinos' => fn ($q) => $q->where('casinos.status', 'published')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($t) => $t->casinos_count > 0);

        $metaTitle = 'Browse by tag | '.SeoSetting::get('site_name', 'RoyalCasinoHub');
        $metaDescription = 'Find online casinos by category and tags.';

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Browse', 'item' => null],
            ],
        ];

        return view('browse.index', [
            'tags' => $tags,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => url('/browse'),
            'breadcrumbSchema' => $breadcrumbSchema,
        ]);
    }

    public function tag(Request $request, string $tagSlug)
    {
        $tag = Tag::where('slug', $tagSlug)->firstOrFail();

        $sort = $request->query('sort', 'name');
        if (! in_array($sort, ['name', 'top-rated', 'most-reviewed', 'newest'], true)) {
            $sort = 'name';
        }

        $query = Casino::published()
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->withCount('approvedReviews');

        match ($sort) {
            'top-rated' => $query->orderByDesc('average_rating')->orderBy('name'),
            'most-reviewed' => $query->orderByDesc('reviews_count')->orderBy('name'),
            'newest' => $query->latest(),
            default => $query->orderBy('country')->orderBy('name'),
        };

        $casinos = $query->paginate(24)->withQueryString();

        $country = $tag->name;
        $metaTitle = "{$tag->name} casinos | ".SeoSetting::get('site_name', 'RoyalCasinoHub');
        $metaDescription = "Browse {$tag->name} online casinos. Sort by rating, reviews, or newest.";

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Browse', 'item' => url('/browse')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $tag->name, 'item' => null],
            ],
        ];

        $canonical = $casinos->url($casinos->currentPage());

        $prevPage = $casinos->currentPage() > 1
            ? $casinos->previousPageUrl()
            : null;
        $nextPage = $casinos->hasMorePages()
            ? $casinos->nextPageUrl()
            : null;

        return view('browse.tag', [
            'tag' => $tag,
            'casinos' => $casinos,
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
