<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\SeoSetting;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function show(Request $request)
    {
        $slugs = array_filter(array_map('trim', explode(',', (string) $request->query('casinos', ''))));
        $slugs = array_slice(array_unique($slugs), 0, 3);

        $casinos = $slugs === []
            ? collect()
            : Casino::published()->whereIn('slug', $slugs)->get()->sortBy(function ($c) use ($slugs) {
                $i = array_search($c->slug, $slugs, true);

                return $i !== false ? $i : 999;
            })->values();

        $metaTitle = 'Compare casinos | '.SeoSetting::get('site_name', 'RoyalCasinoHub');
        $metaDescription = 'Side-by-side comparison of up to three online casinos.';

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Compare', 'item' => null],
            ],
        ];

        return view('compare.show', [
            'casinos' => $casinos,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => $request->filled('casinos')
                ? url('/compare?casinos='.rawurlencode((string) $request->query('casinos')))
                : url('/compare'),
            'breadcrumbSchema' => $breadcrumbSchema,
        ]);
    }
}
