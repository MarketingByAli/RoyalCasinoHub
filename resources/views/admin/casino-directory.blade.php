@extends('layouts.admin')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-amber-400">Casino directory insights</h1>
        <p class="text-sm text-gray-500 mt-1">Overview of all listings: totals, status, countries, and coverage.</p>
    </div>
    <a href="{{ route('admin.casinos.index') }}" class="text-sm text-amber-400 hover:underline">← Back to casino list</a>
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
    <div class="mb-8 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
        <strong>{{ number_format($stats['pending_user_submissions']) }}</strong> listing(s) pending from user submissions (awaiting payment / review).
        <a href="{{ route('admin.casinos.index', ['status' => 'pending']) }}" class="text-amber-400 hover:underline ml-2">View pending in list</a>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-slate-800/40 border border-amber-900/30 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-amber-900/30 flex items-center justify-between gap-2">
            <h2 class="text-sm font-semibold text-gray-200">By country</h2>
            <span class="text-xs text-gray-500">{{ $stats['by_country']->count() }} countries</span>
        </div>
        <div class="max-h-[28rem] overflow-y-auto">
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
                                <a href="{{ route('admin.casinos.index', ['country_slug' => $row->country_slug]) }}" class="text-amber-400 hover:underline">{{ $row->country }}</a>
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
        <p class="text-xs text-gray-600 px-4 py-3 border-t border-amber-900/20">Click a country to open the casino list filtered to that country.</p>
    </div>
    <div class="bg-slate-800/40 border border-amber-900/30 rounded-xl p-4 h-fit">
        <h2 class="text-sm font-semibold text-gray-200 mb-3">By status</h2>
        <ul class="space-y-2 text-sm">
            <li class="flex justify-between"><span class="text-gray-400">Published</span><span class="text-emerald-400 font-medium">{{ number_format($published) }}</span></li>
            <li class="flex justify-between"><span class="text-gray-400">Draft</span><span class="text-gray-300 font-medium">{{ number_format($draft) }}</span></li>
            <li class="flex justify-between"><span class="text-gray-400">Pending</span><span class="text-amber-400 font-medium">{{ number_format($pending) }}</span></li>
        </ul>
        <p class="text-xs text-gray-600 mt-4">Counts include every casino in the database.</p>
    </div>
</div>
@endsection
