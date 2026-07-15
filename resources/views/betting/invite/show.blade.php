@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto text-center py-12">
    <h1 class="text-2xl font-bold text-amber-400 mb-4">You're invited to a challenge</h1>
    <p class="text-gray-400 mb-6">{{ $market->creator->bettingProfile?->display_name ?? $market->creator->name }} wants to bet on:</p>
    <div class="p-6 rounded-xl border border-amber-900/30 bg-slate-900/50 text-left mb-8">
        <h2 class="text-xl font-semibold text-white">{{ $market->title }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $market->event?->title }}</p>
        <p class="mt-4 text-sm">Stake: <span class="text-amber-400">{{ number_format($market->stake_amount, 0) }} play points each</span></p>
        <p class="text-sm">Creator's pick: {{ $market->creator_outcome }}</p>
    </div>
    @auth
        @if(auth()->user()->bettingProfile)
            <a href="{{ route('betting.challenges.show', $market) }}" class="inline-block bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-6 py-3 rounded-xl">Review & accept</a>
        @else
            <a href="{{ route('betting.onboarding') }}" class="inline-block bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-6 py-3 rounded-xl">Complete profile first</a>
        @endif
    @else
        <a href="{{ route('register') }}" class="inline-block bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-6 py-3 rounded-xl mr-3">Register</a>
        <a href="{{ route('login') }}" class="inline-block border border-amber-700 text-amber-400 px-6 py-3 rounded-xl">Log in</a>
    @endauth
</div>
@endsection
