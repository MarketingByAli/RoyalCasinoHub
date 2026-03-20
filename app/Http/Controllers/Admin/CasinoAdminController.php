<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Services\EnrichmentService;
use Illuminate\Http\Request;
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
            $query->where('name', 'like', '%' . $search . '%');
        }

        $casinos = $query->latest()->paginate(25);

        return view('admin.casinos.index', compact('casinos'));
    }

    public function edit(Casino $casino)
    {
        return view('admin.casinos.edit', compact('casino'));
    }

    public function update(Request $request, Casino $casino)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:casinos,slug,' . $casino->id,
            'country' => 'required|string|max:255',
            'country_slug' => 'required|string|max:255',
            'website' => 'nullable|url|max:500',
            'logo_url' => 'nullable|url|max:500',
            'logo_alt' => 'nullable|string|max:255',
            'screenshot_url' => 'nullable|url|max:500',
            'screenshot_alt' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:50000',
            'short_description' => 'nullable|string|max:500',
            'status' => 'required|in:published,draft,pending',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'robots' => 'nullable|string|max:50',
        ]);

        $status = $validated['status'];
        unset($validated['status']);
        $casino->update($validated);
        $casino->status = $status;
        $casino->save();

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
        $casino->save();
        return back()->with('success', 'Enrichment jobs queued.');
    }
}
