<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\CasinoDailyView;
use Illuminate\Http\Request;

class CasinoOwnerController extends Controller
{
    public function index(Request $request)
    {
        $casinos = Casino::where('claimed_by_user_id', $request->user()->id)
            ->withCount('approvedReviews')
            ->latest()
            ->paginate(10);

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'My Listings', 'item' => null],
            ],
        ];

        return view('casino-owner.index', [
            'casinos' => $casinos,
            'meta_title' => 'My Claimed Listings | RoyalCasinoHub',
            'meta_description' => 'Manage your claimed casino listings on RoyalCasinoHub.',
            'canonical' => url('/my-listings'),
            'breadcrumbSchema' => $breadcrumbSchema,
        ]);
    }

    public function edit(Casino $casino, Request $request)
    {
        if (!$casino->canBeEditedBy($request->user())) {
            abort(403);
        }

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'My Listings', 'item' => url('/my-listings')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Edit ' . $casino->name, 'item' => null],
            ],
        ];

        return view('casino-owner.edit', [
            'casino' => $casino,
            'meta_title' => "Edit {$casino->name} | RoyalCasinoHub",
            'meta_description' => "Edit your casino listing for {$casino->name}.",
            'canonical' => route('casino-owner.edit', $casino),
            'breadcrumbSchema' => $breadcrumbSchema,
        ]);
    }

    public function update(Request $request, Casino $casino)
    {
        if (!$casino->canBeEditedBy($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => 'nullable|string|max:50000',
            'short_description' => 'nullable|string|max:500',
            'logo_url' => 'nullable|url|max:500',
            'logo_alt' => 'nullable|string|max:255',
            'screenshot_url' => 'nullable|url|max:500',
            'screenshot_alt' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $casino->update($validated);

        return back()->with('success', 'Listing updated.');
    }

    public function analytics(Casino $casino, Request $request)
    {
        if (! $casino->canBeEditedBy($request->user())) {
            abort(403);
        }

        $series = CasinoDailyView::query()
            ->where('casino_id', $casino->id)
            ->where('day', '>=', now()->subDays(30)->toDateString())
            ->orderBy('day')
            ->get(['day', 'views']);

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'My Listings', 'item' => url('/my-listings')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Analytics', 'item' => null],
            ],
        ];

        return view('casino-owner.analytics', [
            'casino' => $casino,
            'series' => $series,
            'meta_title' => "Analytics — {$casino->name} | RoyalCasinoHub",
            'meta_description' => 'Listing traffic for your casino.',
            'canonical' => route('casino-owner.analytics', $casino),
            'breadcrumbSchema' => $breadcrumbSchema,
            'noindex' => true,
        ]);
    }
}
