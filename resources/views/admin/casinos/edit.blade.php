@extends('layouts.admin')

@php
    $adminOffer = $casino->offers->where('source', 'admin')->first();
    $paymentText = old('payment_methods_text');
    if ($paymentText === null) {
        $paymentText = is_array($casino->payment_methods) ? implode("\n", $casino->payment_methods) : '';
    }
    $softwareText = old('software_providers_text');
    if ($softwareText === null) {
        $softwareText = is_array($casino->software_providers) ? implode("\n", $casino->software_providers) : '';
    }
    $galleryText = old('gallery_urls_text');
    if ($galleryText === null) {
        $galleryText = is_array($casino->gallery_urls) ? implode("\n", $casino->gallery_urls) : '';
    }
    $supportJson = old('support_channels_json');
    if ($supportJson === null) {
        $supportJson = $casino->support_channels
            ? json_encode($casino->support_channels, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            : '';
    }
    $selectedTagIds = old('tag_ids', $casino->tags->pluck('id')->all());
@endphp

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">Edit Casino: {{ $casino->name }}</h1>

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-900/50 bg-red-950/40 px-4 py-3 text-sm text-red-200">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<p class="text-sm text-gray-500 mb-6">Profile completeness: <span class="text-amber-400 font-semibold">{{ $casino->profile_completeness ?? 0 }}%</span> (recalculated on save)</p>

<form action="{{ route('admin.casinos.update', $casino) }}" method="POST" class="max-w-3xl space-y-8">
    @csrf
    @method('PUT')
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-200 border-b border-amber-900/30 pb-2">Basics</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $casino->name) }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $casino->slug) }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Country</label>
                <input type="text" name="country" value="{{ old('country', $casino->country) }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Country Slug</label>
                <input type="text" name="country_slug" value="{{ old('country_slug', $casino->country_slug) }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Region</label>
                <input type="text" name="region" value="{{ old('region', $casino->region) }}" placeholder="e.g. Nevada, Madrid" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Locality</label>
                <input type="text" name="locality" value="{{ old('locality', $casino->locality) }}" placeholder="e.g. London, Los Angeles" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Website</label>
            <input type="url" name="website" value="{{ old('website', $casino->website) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">LinkedIn</label>
            <input type="url" name="social_linkedin" value="{{ old('social_linkedin', $casino->social_links['linkedin'] ?? '') }}" placeholder="https://www.linkedin.com/company/..." class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Logo URL</label>
                <input type="url" name="logo_url" value="{{ old('logo_url', $casino->logo_url) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Logo Alt</label>
                <input type="text" name="logo_alt" value="{{ old('logo_alt', $casino->logo_alt) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Screenshot URL</label>
                <input type="url" name="screenshot_url" value="{{ old('screenshot_url', $casino->screenshot_url) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Screenshot Alt</label>
                <input type="text" name="screenshot_alt" value="{{ old('screenshot_alt', $casino->screenshot_alt) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Description</label>
            <textarea name="description" rows="6" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">{{ old('description', $casino->description) }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Short Description</label>
            <input type="text" name="short_description" value="{{ old('short_description', $casino->short_description) }}" maxlength="500" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-200 border-b border-amber-900/30 pb-2">License &amp; facts</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">License</label>
                <input type="text" name="license" value="{{ old('license', $casino->license) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">License authority slug</label>
                <input type="text" name="license_authority_slug" value="{{ old('license_authority_slug', $casino->license_authority_slug) }}" placeholder="mga, ukgc" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Founded (year)</label>
                <input type="number" name="established_year" value="{{ old('established_year', $casino->established_year) }}" min="1900" max="2100" placeholder="e.g. 2021" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Last verified</label>
                <input type="datetime-local" name="last_verified_at" value="{{ old('last_verified_at', $casino->last_verified_at?->format('Y-m-d\TH:i')) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Min deposit</label>
                <input type="number" step="0.01" name="min_deposit" value="{{ old('min_deposit', $casino->min_deposit) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Withdrawal time</label>
                <input type="text" name="withdrawal_time_text" value="{{ old('withdrawal_time_text', $casino->withdrawal_time_text) }}" placeholder="e.g. 24–48h" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-200 border-b border-amber-900/30 pb-2">Pros &amp; cons</h2>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Pros</label>
            <textarea name="pros" rows="4" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">{{ old('pros', $casino->pros) }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Cons</label>
            <textarea name="cons" rows="4" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">{{ old('cons', $casino->cons) }}</textarea>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-200 border-b border-amber-900/30 pb-2">Payments, support &amp; software</h2>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Payment methods (one per line)</label>
            <textarea name="payment_methods_text" rows="5" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 font-mono text-sm">{{ $paymentText }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Support channels (JSON object)</label>
            <textarea name="support_channels_json" rows="6" placeholder='{"live_chat": true, "email": "support@..."}' class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 font-mono text-sm">{{ $supportJson }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Software providers (one per line)</label>
            <textarea name="software_providers_text" rows="4" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 font-mono text-sm">{{ $softwareText }}</textarea>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-200 border-b border-amber-900/30 pb-2">Gallery</h2>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Image URLs (one per line)</label>
            <textarea name="gallery_urls_text" rows="4" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 font-mono text-sm">{{ $galleryText }}</textarea>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-200 border-b border-amber-900/30 pb-2">Visibility</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Tier</label>
                <select name="tier" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
                    <option value="standard" {{ old('tier', $casino->tier ?? 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="featured" {{ old('tier', $casino->tier ?? 'standard') === 'featured' ? 'selected' : '' }}>Featured</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Featured until</label>
                <input type="datetime-local" name="featured_until" value="{{ old('featured_until', $casino->featured_until?->format('Y-m-d\TH:i')) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-200 border-b border-amber-900/30 pb-2">Tags</h2>
        <div class="flex flex-wrap gap-3">
            @foreach ($tags as $tag)
                <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" {{ in_array($tag->id, $selectedTagIds, true) ? 'checked' : '' }} class="rounded border-amber-900/40 bg-slate-800">
                    {{ $tag->name }}
                </label>
            @endforeach
        </div>
        @if ($tags->isEmpty())
            <p class="text-sm text-gray-500">No tags yet. Create tags in the database or a future admin screen.</p>
        @endif
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-200 border-b border-amber-900/30 pb-2">Primary offer (admin)</h2>
        <p class="text-sm text-gray-500">Leave all empty to remove the admin-sourced offer for this casino.</p>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Title</label>
            <input type="text" name="offer_title" value="{{ old('offer_title', $adminOffer?->title) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Welcome bonus text</label>
            <textarea name="offer_welcome_bonus_text" rows="3" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">{{ old('offer_welcome_bonus_text', $adminOffer?->welcome_bonus_text) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Wagering requirement</label>
                <input type="text" name="offer_wagering_requirement" value="{{ old('offer_wagering_requirement', $adminOffer?->wagering_requirement) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Free spins</label>
                <input type="number" name="offer_free_spins" value="{{ old('offer_free_spins', $adminOffer?->free_spins) }}" min="0" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Offer expires</label>
            <input type="datetime-local" name="offer_expires_at" value="{{ old('offer_expires_at', $adminOffer?->expires_at?->format('Y-m-d\TH:i')) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-200 border-b border-amber-900/30 pb-2">Publishing &amp; SEO</h2>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Status</label>
            <select name="status" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
                <option value="published" {{ old('status', $casino->status) === 'published' ? 'selected' : '' }}>Published</option>
                <option value="draft" {{ old('status', $casino->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="pending" {{ old('status', $casino->status) === 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $casino->meta_title) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Robots</label>
                <input type="text" name="robots" value="{{ old('robots', $casino->robots) }}" placeholder="index,follow" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Meta Description</label>
            <textarea name="meta_description" rows="2" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">{{ old('meta_description', $casino->meta_description) }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Canonical URL</label>
            <input type="url" name="canonical_url" value="{{ old('canonical_url', $casino->canonical_url) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>

    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-6 py-2 rounded-lg">Save</button>
</form>
@endsection
