<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Services\CasinoIntakeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CasinoListingSubmissionController extends Controller
{
    public function create()
    {
        $this->authorize('create', Casino::class);

        return view('casino-listings.create', [
            'meta_title' => 'Submit a casino listing | RoyalCasinoHub',
            'meta_description' => 'Propose a new casino to be listed on RoyalCasinoHub.',
            'canonical' => route('casino-listings.create'),
            'noindex' => true,
        ]);
    }

    public function store(Request $request, CasinoIntakeService $intake)
    {
        $this->authorize('create', Casino::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'website' => 'nullable|string|max:500',
            'region' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:500',
            'founded' => 'nullable|string|max:32',
        ]);

        $website = ! empty($validated['website']) ? $intake->normalizeImportUrl(trim($validated['website'])) : null;
        $linkedin = ! empty($validated['linkedin']) ? $intake->normalizeImportUrl(trim($validated['linkedin'])) : null;
        $region = isset($validated['region']) && trim($validated['region']) !== '' ? trim($validated['region']) : null;
        $locality = isset($validated['locality']) && trim($validated['locality']) !== '' ? trim($validated['locality']) : null;

        $foundedParsed = $intake->parseFoundedYearString(isset($validated['founded']) ? trim($validated['founded']) : null);
        if ($foundedParsed['error'] !== null) {
            return back()->withInput()->withErrors(['founded' => $foundedParsed['error']]);
        }

        $urlValidator = Validator::make(
            [
                'website' => $website,
                'linkedin' => $linkedin,
            ],
            [
                'website' => 'nullable|url|max:500',
                'linkedin' => 'nullable|url|max:500',
            ],
            [
                'website.url' => 'Website must be a valid URL.',
                'linkedin.url' => 'LinkedIn must be a valid URL.',
            ]
        );
        if ($urlValidator->fails()) {
            return back()->withInput()->withErrors($urlValidator);
        }

        $dup = $intake->duplicateRowMessage($website, $validated['name'], $validated['country']);
        if ($dup !== null) {
            return back()->withInput()->withErrors(['name' => $dup]);
        }

        $slug = $intake->uniqueSlugForName($validated['name']);
        $casino = Casino::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'country' => $validated['country'],
            'country_slug' => Str::slug($validated['country']),
            'region' => $region,
            'locality' => $locality,
            'website' => $website,
            'social_links' => Casino::mergeSocialLinks(null, $linkedin),
            'established_year' => $foundedParsed['year'],
        ]);
        $casino->status = 'pending';
        $casino->submitted_by_user_id = $request->user()->id;
        $casino->listing_fee_paid_at = null;
        $casino->enrichment_status = 'pending';
        $casino->save();

        return redirect()->route('account.submitted-listings')
            ->with('success', 'Your listing was submitted. It stays pending until payment and admin review.');
    }
}
