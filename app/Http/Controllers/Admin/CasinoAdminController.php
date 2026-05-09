<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Models\CasinoOffer;
use App\Models\EnrichmentQueue;
use App\Services\ActivityLogger;
use App\Services\CasinoIntakeService;
use App\Services\EnrichmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        if ($request->filled('country_slug')) {
            $query->where('country_slug', $request->country_slug);
        }

        $casinos = $query->latest()->paginate(25);

        return view('admin.casinos.index', compact('casinos'));
    }

    public function directoryInsights()
    {
        return view('admin.casino-directory', [
            'stats' => $this->casinoDirectoryStats(),
        ]);
    }

    /**
     * @return array{total: int, by_status: \Illuminate\Support\Collection, with_website: int, claimed: int, pending_user_submissions: int, by_country: \Illuminate\Support\Collection}
     */
    private function casinoDirectoryStats(): array
    {
        return [
            'total' => Casino::count(),
            'by_status' => Casino::query()
                ->selectRaw('status, COUNT(*) as n')
                ->groupBy('status')
                ->pluck('n', 'status'),
            'with_website' => Casino::query()->whereNotNull('website')->where('website', '!=', '')->count(),
            'claimed' => Casino::query()->where('is_claimed', true)->count(),
            'pending_user_submissions' => Casino::query()
                ->where('status', 'pending')
                ->whereNotNull('submitted_by_user_id')
                ->count(),
            'by_country' => Casino::query()
                ->selectRaw('country, country_slug, COUNT(*) as n')
                ->groupBy('country', 'country_slug')
                ->orderByDesc('n')
                ->orderBy('country')
                ->get(),
        ];
    }

    public function create()
    {
        return view('admin.casinos.create');
    }

    public function store(Request $request, CasinoIntakeService $intake, EnrichmentService $enrichmentService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'website' => 'nullable|string|max:500',
            'region' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:500',
            'founded' => 'nullable|string|max:32',
            'status' => 'required|in:published,draft,pending',
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
        $status = $validated['status'];

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
        $casino->status = $status;
        $casino->submitted_by_user_id = null;
        $casino->listing_fee_paid_at = now();
        $casino->enrichment_status = 'pending';
        $casino->save();

        if ($status === 'published') {
            $enrichmentService->createEnrichmentJobs($casino);
        }

        ActivityLogger::log('casino.created', $casino);

        return redirect()->route('admin.casinos.edit', $casino)->with('success', 'Casino created.');
    }

    public function edit(Casino $casino)
    {
        $casino->load('offers');

        return view('admin.casinos.edit', compact('casino'));
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
        EnrichmentQueue::where('casino_id', $casino->id)->update([
            'status' => 'pending',
            'attempts' => 0,
            'result' => null,
            'last_attempted_at' => null,
        ]);
        $casino->enrichment_status = 'pending';
        $casino->enrichment_last_error = null;
        $casino->save();

        return back()->with('success', 'Enrichment jobs queued.');
    }
}
