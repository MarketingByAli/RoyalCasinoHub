@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-amber-400">Casinos</h1>
    <a href="{{ route('admin.casinos.create') }}" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-4 py-2 rounded-lg text-sm">Add casino</a>
</div>

@php
    $byStatus = $stats['by_status'] ?? collect();
    $published = (int) ($byStatus['published'] ?? 0);
    $draft = (int) ($byStatus['draft'] ?? 0);
    $pending = (int) ($byStatus['pending'] ?? 0);
@endphp

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-4">
        <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total casinos</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-4">
        <p class="text-2xl font-bold text-emerald-400">{{ number_format($published) }}</p>
        <p class="text-xs text-gray-500 mt-1">Published</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-4">
        <p class="text-2xl font-bold text-gray-400">{{ number_format($draft) }}</p>
        <p class="text-xs text-gray-500 mt-1">Draft</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-4">
        <p class="text-2xl font-bold text-amber-400">{{ number_format($pending) }}</p>
        <p class="text-xs text-gray-500 mt-1">Pending</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-4">
        <p class="text-2xl font-bold text-sky-400">{{ number_format($stats['with_website']) }}</p>
        <p class="text-xs text-gray-500 mt-1">With website</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-4">
        <p class="text-2xl font-bold text-violet-400">{{ number_format($stats['claimed']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Claimed listings</p>
    </div>
</div>

@if($stats['pending_user_submissions'] > 0)
    <div class="mb-6 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
        <strong>{{ number_format($stats['pending_user_submissions']) }}</strong> listing(s) pending from user submissions (awaiting payment / review).
        <a href="{{ route('admin.casinos.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}" class="text-amber-400 hover:underline ml-2">View pending</a>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-slate-800/40 border border-amber-900/30 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-amber-900/30 flex items-center justify-between gap-2">
            <h2 class="text-sm font-semibold text-gray-200">By country</h2>
            <span class="text-xs text-gray-500">{{ $stats['by_country']->count() }} countries</span>
        </div>
        <div class="max-h-64 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 border-b border-amber-900/20 sticky top-0 bg-slate-900/90">
                    <tr>
                        <th class="py-2 px-4 font-medium">Country</th>
                        <th class="py-2 px-4 font-medium text-right w-24">Count</th>
                        <th class="py-2 px-4 font-medium text-right w-28">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['by_country'] as $row)
                        @php $pct = $stats['total'] > 0 ? round(100 * $row->n / $stats['total'], 1) : 0; @endphp
                        <tr class="border-b border-amber-900/10 hover:bg-slate-800/50">
                            <td class="py-2 px-4">
                                <a href="{{ route('admin.casinos.index', array_merge(request()->except('page'), ['country_slug' => $row->country_slug])) }}" class="text-amber-400 hover:underline">{{ $row->country }}</a>
                            </td>
                            <td class="py-2 px-4 text-right text-gray-300 font-medium">{{ number_format($row->n) }}</td>
                            <td class="py-2 px-4 text-right text-gray-500">{{ $pct }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 px-4 text-center text-gray-500">No casinos yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="bg-slate-800/40 border border-amber-900/30 rounded-xl p-4">
        <h2 class="text-sm font-semibold text-gray-200 mb-3">By status</h2>
        <ul class="space-y-2 text-sm">
            <li class="flex justify-between"><span class="text-gray-400">Published</span><span class="text-emerald-400 font-medium">{{ number_format($published) }}</span></li>
            <li class="flex justify-between"><span class="text-gray-400">Draft</span><span class="text-gray-300 font-medium">{{ number_format($draft) }}</span></li>
            <li class="flex justify-between"><span class="text-gray-400">Pending</span><span class="text-amber-400 font-medium">{{ number_format($pending) }}</span></li>
        </ul>
        <p class="text-xs text-gray-600 mt-4">Totals match all casinos in the database (ignores filters below).</p>
    </div>
</div>

@if(request('country_slug'))
    <div class="mb-4">
        <a href="{{ route('admin.casinos.index', request()->except(['country_slug', 'page'])) }}" class="text-sm text-amber-400 hover:underline">Clear country filter</a>
        <span class="text-gray-500 text-sm ml-2">Showing casinos in this country only.</span>
    </div>
@endif

<form method="GET" class="flex flex-wrap gap-4 mb-6">
    @if(request('country_slug'))
        <input type="hidden" name="country_slug" value="{{ request('country_slug') }}">
    @endif
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    <select name="status" class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        <option value="">All Status</option>
        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
    </select>
    <select name="enrichment" class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        <option value="">All Enrichment</option>
        <option value="pending" {{ request('enrichment') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="done" {{ request('enrichment') === 'done' ? 'selected' : '' }}>Done</option>
    </select>
    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 px-4 py-2 rounded-lg">Filter</button>
</form>

<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-amber-900/30">
                <th class="text-left py-3 px-4">Name</th>
                <th class="text-left py-3 px-4">Country</th>
                <th class="text-left py-3 px-4">Status</th>
                <th class="text-left py-3 px-4">Enrichment</th>
                <th class="text-left py-3 px-4">Reviews</th>
                <th class="text-left py-3 px-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($casinos as $casino)
                <tr class="border-b border-amber-900/20 hover:bg-slate-800/30">
                    <td class="py-3 px-4">
                        <a href="{{ route('casino.show', $casino->slug) }}" class="text-amber-400 hover:underline" target="_blank" rel="noopener">{{ $casino->name }}</a>
                    </td>
                    <td class="py-3 px-4 text-gray-400">{{ $casino->country }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-sm {{ $casino->status === 'published' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-gray-500/20 text-gray-400' }}">{{ $casino->status }}</span>
                    </td>
                    <td class="py-3 px-4 text-gray-400">{{ $casino->enrichment_status }}</td>
                    <td class="py-3 px-4">{{ $casino->approved_reviews_count }}</td>
                    <td class="py-3 px-4 flex gap-2">
                        <a href="{{ route('admin.casinos.edit', $casino) }}" class="text-amber-400 hover:underline text-sm">Edit</a>
                        <form action="{{ route('admin.casinos.toggle-status', $casino) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-amber-400 hover:underline text-sm">{{ $casino->status === 'published' ? 'Unpublish' : 'Publish' }}</button>
                        </form>
                        <form action="{{ route('admin.casinos.queue-enrichment', $casino) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-amber-400 hover:underline text-sm">Re-enrich</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $casinos->withQueryString()->links() }}</div>
@endsection
