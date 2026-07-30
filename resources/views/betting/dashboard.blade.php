@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-amber-400 font-serif">Challenges</h1>
            <p class="text-gray-500 text-sm mt-1">Play-money friend challenges — no real cash.</p>
        </div>
        <a href="{{ route('betting.challenges.create') }}" class="bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-4 py-2 rounded-lg">New challenge</a>
    </div>

    @if($wallet = auth()->user()->bettingWallet)
        <div class="mb-6 p-4 rounded-xl border border-amber-900/30 bg-slate-900/50 flex flex-wrap gap-6">
            <div><span class="text-gray-500 text-sm">Available</span><p class="text-xl font-semibold text-white">{{ number_format($wallet->available, 0) }} pts</p></div>
            <div><span class="text-gray-500 text-sm">Locked</span><p class="text-xl font-semibold text-amber-400">{{ number_format($wallet->locked, 0) }} pts</p></div>
            <a href="{{ route('betting.wallet') }}" class="text-amber-400 text-sm self-end hover:underline">Wallet details</a>
        </div>
    @endif

    <div class="space-y-4">
        @forelse($recentMarkets as $market)
            <a href="{{ route('betting.challenges.show', $market) }}" class="block p-4 rounded-xl border border-amber-900/25 bg-slate-900/40 hover:border-amber-700/40 transition-colors">
                <div class="flex justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-white">{{ $market->title }}</h2>
                        <p class="text-sm text-gray-500">{{ $market->event?->title }} · {{ $market->status->value }}</p>
                    </div>
                    <span class="text-amber-400 font-medium">{{ number_format($market->stake_amount, 0) }} pts</span>
                </div>
            </a>
        @empty
            <p class="text-gray-500">No challenges yet. Create one and invite a friend!</p>
        @endforelse
    </div>

    <div class="mt-8 flex gap-4 flex-wrap" x-data="{ unread: 0 }" x-init="
        const load = () => fetch('{{ route('betting.notifications.unread-count') }}', { headers: { 'Accept': 'application/json' }})
            .then(r => r.json()).then(d => unread = d.count).catch(() => {});
        load(); setInterval(load, 30000);
    ">
        <a href="{{ route('betting.challenges.index') }}" class="text-amber-400 hover:underline">All my challenges</a>
        <a href="{{ route('betting.explore.markets') }}" class="text-amber-400 hover:underline">Explore</a>
        <a href="{{ route('betting.explore.events') }}" class="text-amber-400 hover:underline">Events</a>
        <a href="{{ route('betting.leaderboard.weekly') }}" class="text-amber-400 hover:underline">Leaderboard</a>
        <a href="{{ route('betting.notifications.index') }}" class="text-amber-400 hover:underline">
            Notifications <span x-show="unread > 0" x-text="'('+unread+')'" class="text-amber-300"></span>
        </a>
        <a href="{{ route('betting.rg.edit') }}" class="text-amber-400 hover:underline">Responsible gambling</a>
        @if(auth()->user()->bettingProfile)
            <a href="{{ route('betting.profiles.show', auth()->user()->bettingProfile->username) }}" class="text-amber-400 hover:underline">My profile</a>
        @endif
    </div>
</div>
@endsection
