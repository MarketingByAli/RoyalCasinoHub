@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
        <p class="text-gray-400 text-sm">Total Casinos</p>
        <p class="text-2xl font-bold text-white mt-1">{{ $stats['casinos'] }}</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
        <p class="text-gray-400 text-sm">Published</p>
        <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['published_casinos'] }}</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
        <p class="text-gray-400 text-sm">Pending Reviews</p>
        <p class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['pending_reviews'] }}</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
        <p class="text-gray-400 text-sm">Pending Claims</p>
        <p class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['pending_claims'] }}</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
        <p class="text-gray-400 text-sm">Enrichment Queue</p>
        <p class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['pending_enrichment'] }}</p>
    </div>
</div>

<h2 class="text-lg font-semibold text-gray-300 mt-10 mb-4">Reviews per day (14 days)</h2>
<div class="flex items-end gap-1 h-40 border border-amber-900/20 rounded-xl p-4 bg-slate-800/30">
    @php $max = max(1, ...array_values($reviewTrend)); @endphp
    @foreach($reviewTrend as $day => $count)
        <div class="flex-1 flex flex-col justify-end items-center group">
            <span class="text-[10px] text-gray-500 mb-1">{{ $count }}</span>
            <div class="w-full bg-amber-500/40 rounded-t group-hover:bg-amber-500/60 transition-colors" style="height: {{ $max ? round(($count / $max) * 100) : 0 }}%"></div>
        </div>
    @endforeach
</div>
<p class="text-xs text-gray-600 mt-2">Oldest ← → Newest</p>
@endsection
