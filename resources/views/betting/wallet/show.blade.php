@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 mb-6">Play-money wallet</h1>
    <div class="p-6 rounded-xl border border-amber-900/30 bg-slate-900/50 space-y-4">
        <p class="text-sm text-amber-500/90">Play money only — no cash value, no withdrawals.</p>
        <dl class="grid grid-cols-2 gap-4">
            <div><dt class="text-gray-500 text-sm">Available</dt><dd class="text-2xl font-bold text-white">{{ number_format($wallet->available, 0) }}</dd></div>
            <div><dt class="text-gray-500 text-sm">Locked in bets</dt><dd class="text-2xl font-bold text-amber-400">{{ number_format($wallet->locked, 0) }}</dd></div>
        </dl>
        <p class="text-xs text-gray-600">Currency: {{ $wallet->currency }}</p>
    </div>
</div>
@endsection
