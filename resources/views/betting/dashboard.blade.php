@extends('layouts.app')

@section('content')
@php
    $statusLabels = [
        'open' => 'Open',
        'partially_matched' => 'Filling',
        'fully_matched' => 'Matched',
        'locked' => 'Locked',
        'in_progress' => 'In progress',
        'pending_result' => 'Awaiting result',
        'result_published' => 'Result posted',
        'dispute_window' => 'Dispute window',
        'under_dispute' => 'Under dispute',
        'settled' => 'Settled',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
        'voided' => 'Voided',
        'pending_review' => 'In review',
        'rejected' => 'Rejected',
    ];
@endphp
<div class="max-w-4xl mx-auto">
    <div class="flex flex-wrap justify-between items-end gap-4 mb-10">
        <div class="max-w-xl">
            <h1 class="text-3xl font-bold text-amber-400 font-serif tracking-tight">Challenges</h1>
            <p class="text-gray-400 text-sm mt-2 leading-relaxed">
                Create private or public challenges on upcoming events. Stakes come from your wallet credits.
            </p>
        </div>
        <a href="{{ route('betting.challenges.create') }}" class="bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-5 py-2.5 rounded-xl shrink-0">
            New challenge
        </a>
    </div>

    @if($wallet)
        <div class="mb-8 p-5 rounded-2xl border border-amber-900/30 bg-slate-900/50">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="flex flex-wrap gap-8">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Available credits</p>
                        <p class="text-2xl font-semibold text-white tabular-nums">{{ number_format($wallet->available, 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Locked in challenges</p>
                        <p class="text-2xl font-semibold text-amber-400 tabular-nums">{{ number_format($wallet->locked, 0) }}</p>
                    </div>
                </div>
                <a href="{{ route('betting.wallet') }}" class="text-sm text-amber-400 hover:text-amber-300 font-medium">Manage wallet →</a>
            </div>
            <p class="text-xs text-gray-600 mt-4">Credits are your in-app balance used to stake challenges. Add funds or withdraw from your wallet.</p>
        </div>
    @endif

    <nav class="flex flex-wrap gap-2 mb-8" aria-label="Challenges navigation">
        @foreach([
            ['route' => 'betting.challenges.index', 'label' => 'My challenges'],
            ['route' => 'betting.explore.markets', 'label' => 'Explore'],
            ['route' => 'betting.explore.events', 'label' => 'Events'],
            ['route' => 'betting.leaderboard.weekly', 'label' => 'Leaderboard'],
            ['route' => 'betting.notifications.index', 'label' => 'Notifications'.($unreadCount > 0 ? ' ('.$unreadCount.')' : '')],
            ['route' => 'betting.rg.edit', 'label' => 'Limits & safety'],
        ] as $link)
            <a href="{{ route($link['route']) }}"
               class="px-3 py-1.5 rounded-lg text-sm border border-amber-900/35 text-gray-300 hover:border-amber-600/50 hover:text-amber-300 transition-colors">
                {{ $link['label'] }}
            </a>
        @endforeach
        @if(auth()->user()->bettingProfile)
            <a href="{{ route('betting.profiles.show', auth()->user()->bettingProfile->username) }}"
               class="px-3 py-1.5 rounded-lg text-sm border border-amber-900/35 text-gray-300 hover:border-amber-600/50 hover:text-amber-300 transition-colors">
                Profile
            </a>
        @endif
    </nav>

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Recent</h2>
        <a href="{{ route('betting.challenges.index') }}" class="text-sm text-amber-400 hover:text-amber-300">View all</a>
    </div>

    <div class="space-y-3">
        @forelse($recentMarkets as $market)
            @php
                $status = $market->status->value;
                $statusLabel = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));
            @endphp
            <a href="{{ route('betting.challenges.show', $market) }}"
               class="block p-4 rounded-xl border border-amber-900/25 bg-slate-900/40 hover:border-amber-700/45 hover:bg-slate-900/60 transition-colors">
                <div class="flex justify-between gap-4 items-start">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-white truncate">{{ $market->title }}</h3>
                        <p class="text-sm text-gray-500 mt-1 truncate">
                            {{ $market->event?->title ?? 'Event' }}
                            <span class="text-gray-600">·</span>
                            {{ $statusLabel }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-amber-400 font-semibold tabular-nums">{{ number_format($market->stake_amount, 0) }}</p>
                        <p class="text-xs text-gray-600">credits stake</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-xl border border-dashed border-amber-900/30 bg-slate-900/30 px-6 py-12 text-center">
                <p class="text-gray-300 font-medium mb-1">No challenges yet</p>
                <p class="text-sm text-gray-500 mb-5">Start one on an upcoming event and invite friends to take the other side.</p>
                <a href="{{ route('betting.challenges.create') }}" class="inline-block bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-5 py-2.5 rounded-xl">
                    Create your first challenge
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
