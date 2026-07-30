@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-amber-400 font-serif mb-2">{{ __('betting.explore_markets') }}</h1>
    <p class="text-gray-500 text-sm mb-8">Open public play-money challenges you can join.</p>
    <div class="space-y-4">
        @forelse($markets as $market)
            <a href="{{ route('betting.challenges.show', $market) }}" class="block p-4 rounded-xl border border-amber-900/25 bg-slate-900/40 hover:border-amber-700/40">
                <div class="flex justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-white">{{ $market->title }}</h2>
                        <p class="text-sm text-gray-500">{{ $market->event?->title }} · {{ $market->status->value }} · {{ $market->participant_cap }} seats</p>
                    </div>
                    <span class="text-amber-400 font-medium">{{ number_format($market->stake_amount, 0) }} pts</span>
                </div>
            </a>
        @empty
            <p class="text-gray-500">No public challenges right now.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $markets->links() }}</div>
</div>
@endsection
