<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Models\CasinoOffer;
use App\Models\Tag;
use App\Services\ActivityLogger;
use App\Services\EnrichmentService;
use Illuminate\Http\Request;

class CasinoAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Casino::query()->withCount('approvedReviews');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('enrichment')) {
            $query->where('enrichment_status', $request->enrichment);
        }
        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], substr($request->search, 0, 255));
            $query->where('name', 'like', '%'.$search.'%');
        }

        $casinos = $query->latest()->paginate(25);

        return view('admin.casinos.index', compact('casinos'));
    }

    public function edit(Casino $casino)
    {
        $tags = Tag::orderBy('name')->get();
        $casino->load('tags', 'offers');

        return view('admin.casinos.edit', compact('casino', 'tags'));
    }

    public function update(Request $request, Casino $casino)
    {
        if ($request->input('social_linkedin') === '') {
            $request->merge(['social_linkedin' => null]);
        }
        foreach (['last_verified_at', 'featured_until', 'offer_expires_at'] as $dt) {
            if ($request->input($dt) === '') {
                $request->merge([$dt => null]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:casinos,slug,'.$casino->id,
            'country' => 'required|string|max:255',
            'country_slug' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:500',
            'social_linkedin' => 'nullable|url|max:500',
            'logo_url' => 'nullable|url|max:500',
            'logo_alt' => 'nullable|string|max:255',
            'screenshot_url' => 'nullable|url|max:500',
            'screenshot_alt' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:50000',
            'short_description' => 'nullable|string|max:500',
            'license' => 'nullable|string|max:255',
            'established_year' => 'nullable|integer|min:1900|max:2100',
            'license_authority_slug' => 'nullable|string|max:64',
            'min_deposit' => 'nullable|numeric|min:0',
            'withdrawal_time_text' => 'nullable|string|max:255',
            'pros' => 'nullable|string|max:10000',
            'cons' => 'nullable|string|max:10000',
            'payment_methods_text' => 'nullable|string|max:10000',
            'support_channels_json' => 'nullable|string|max:10000',
            'software_providers_text' => 'nullable|string|max:5000',
            'gallery_urls_text' => 'nullable|string|max:10000',
            'last_verified_at' => 'nullable|date',
            'tier' => 'nullable|in:standard,featured',
            'featured_until' => 'nullable|date',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
            'offer_title' => 'nullable|string|max:255',
            'offer_welcome_bonus_text' => 'nullable|string|max:2000',
            'offer_wagering_requirement' => 'nullable|string|max:255',
            'offer_free_spins' => 'nullable|integer|min:0',
            'offer_expires_at' => 'nullable|date',
            'status' => 'required|in:published,draft,pending',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'robots' => 'nullable|string|max:50',
        ]);

        $status = $validated['status'];
        unset($validated['status']);

        $paymentLines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($validated['payment_methods_text'] ?? ''))));
        $validated['payment_methods'] = $paymentLines !== [] ? array_values($paymentLines) : null;
        unset($validated['payment_methods_text']);

        $galleryLines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($validated['gallery_urls_text'] ?? ''))));
        $validated['gallery_urls'] = $galleryLines !== [] ? array_values($galleryLines) : null;
        unset($validated['gallery_urls_text']);

        $spLines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($validated['software_providers_text'] ?? ''))));
        $validated['software_providers'] = $spLines !== [] ? array_values($spLines) : null;
        unset($validated['software_providers_text']);

        $supportRaw = trim((string) ($validated['support_channels_json'] ?? ''));
        if ($supportRaw !== '') {
            $decoded = json_decode($supportRaw, true);
            $validated['support_channels'] = is_array($decoded) ? $decoded : null;
        } else {
            $validated['support_channels'] = null;
        }
        unset($validated['support_channels_json']);

        $socialLinkedin = $validated['social_linkedin'] ?? null;
        unset($validated['social_linkedin']);
        $validated['social_links'] = Casino::mergeSocialLinks($casino->social_links, $socialLinkedin);

        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $offerFields = [
            'title' => $validated['offer_title'] ?? null,
            'welcome_bonus_text' => $validated['offer_welcome_bonus_text'] ?? null,
            'wagering_requirement' => $validated['offer_wagering_requirement'] ?? null,
            'free_spins' => $validated['offer_free_spins'] ?? null,
            'expires_at' => $validated['offer_expires_at'] ?? null,
        ];
        unset($validated['offer_title'], $validated['offer_welcome_bonus_text'], $validated['offer_wagering_requirement'], $validated['offer_free_spins'], $validated['offer_expires_at']);

        $casino->update($validated);
        $casino->status = $status;
        $casino->save();

        $casino->tags()->sync($tagIds);

        $hasOffer = collect($offerFields)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
        if ($hasOffer) {
            CasinoOffer::updateOrCreate(
                ['casino_id' => $casino->id, 'source' => 'admin'],
                [
                    'title' => $offerFields['title'] ?? 'Current offer',
                    'welcome_bonus_text' => $offerFields['welcome_bonus_text'],
                    'wagering_requirement' => $offerFields['wagering_requirement'],
                    'free_spins' => $offerFields['free_spins'],
                    'expires_at' => $offerFields['expires_at'],
                    'is_active' => true,
                ]
            );
        } else {
            $casino->offers()->where('source', 'admin')->delete();
        }

        $casino->recalculateProfileCompleteness();
        $casino->save();

        ActivityLogger::log('casino.updated', $casino);

        return redirect()->route('admin.casinos.index')->with('success', 'Casino updated.');
    }

    public function toggleStatus(Casino $casino)
    {
        $casino->status = $casino->status === 'published' ? 'draft' : 'published';
        $casino->save();

        return back()->with('success', 'Status updated.');
    }

    public function queueEnrichment(Casino $casino, EnrichmentService $enrichmentService)
    {
        $enrichmentService->createEnrichmentJobs($casino);
        $casino->enrichment_status = 'pending';
        $casino->enrichment_last_error = null;
        $casino->save();

        return back()->with('success', 'Enrichment jobs queued.');
    }
}
