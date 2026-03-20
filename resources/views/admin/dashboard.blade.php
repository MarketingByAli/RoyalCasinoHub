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
@endsection
