<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::published()
            ->orderByDesc('published_at')
            ->paginate(12);

        $metaTitle = 'Blog | '.SeoSetting::get('site_name', 'RoyalCasinoHub');
        $metaDescription = 'Guides, news, and editorial articles about online casinos.';

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => null],
            ],
        ];

        $canonical = $posts->currentPage() === 1
            ? url('/blog')
            : $posts->url($posts->currentPage());

        return view('blog.index', [
            'posts' => $posts,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => $canonical,
            'breadcrumbSchema' => $breadcrumbSchema,
            'prevPage' => $posts->currentPage() > 1 ? $posts->previousPageUrl() : null,
            'nextPage' => $posts->hasMorePages() ? $posts->nextPageUrl() : null,
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        $metaTitle = $post->title.' | '.SeoSetting::get('site_name', 'RoyalCasinoHub');
        $metaDescription = $post->excerpt ?? Str::limit(strip_tags($post->body), 160);

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => url('/blog')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => null],
            ],
        ];

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'description' => $metaDescription,
        ];

        return view('blog.show', [
            'post' => $post,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => url("/blog/{$post->slug}"),
            'breadcrumbSchema' => $breadcrumbSchema,
            'schema' => $schema,
            'ogImage' => null,
            'ogType' => 'article',
        ]);
    }
}
