@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-amber-400 mb-2">{{ __('betting.responsible_gambling') }}</h1>
        <p class="text-gray-500 text-sm">Set play-money stake limits or take a break.</p>
    </div>

    <form method="POST" action="{{ route('betting.rg.limits') }}" class="space-y-4 bg-slate-900/50 border border-amber-900/25 rounded-xl p-6">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm text-gray-300 mb-1">Daily stake limit</label>
            <input type="number" name="daily_stake_limit" value="{{ old('daily_stake_limit', $limits->daily_stake_limit) }}" min="1" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Weekly stake limit</label>
            <input type="number" name="weekly_stake_limit" value="{{ old('weekly_stake_limit', $limits->weekly_stake_limit) }}" min="1" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <button class="w-full bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold py-3 rounded-xl">Save limits</button>
    </form>

    <form method="POST" action="{{ route('betting.rg.cool-off') }}" class="space-y-4 bg-slate-900/50 border border-amber-900/25 rounded-xl p-6">
        @csrf
        <p class="text-sm text-gray-400">Cooling-off blocks new stakes until it ends.</p>
        @if($coolOff)
            <p class="text-amber-400 text-sm">Active until {{ $coolOff->ends_at }}</p>
        @endif
        <select name="hours" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
            <option value="24">24 hours</option>
            <option value="72">72 hours</option>
            <option value="168">7 days</option>
        </select>
        <button class="w-full border border-amber-700 text-amber-400 font-semibold py-3 rounded-xl">Start cool-off</button>
    </form>

    <form method="POST" action="{{ route('betting.rg.self-exclude') }}" class="space-y-4 bg-slate-900/50 border border-red-900/40 rounded-xl p-6" onsubmit="return confirm('Self-exclude this account from betting?')">
        @csrf
        @if($selfExclusion)
            <p class="text-red-400 text-sm">Self-exclusion active{{ $selfExclusion->ends_at ? ' until '.$selfExclusion->ends_at : '' }}.</p>
        @endif
        <select name="days" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
            <option value="30">30 days</option>
            <option value="90">90 days</option>
            <option value="365">365 days</option>
            <option value="">Indefinite</option>
        </select>
        <button class="w-full bg-red-700 hover:bg-red-600 text-white font-semibold py-3 rounded-xl">Self-exclude</button>
    </form>
</div>
@endsection
