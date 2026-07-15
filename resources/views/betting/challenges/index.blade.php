@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 mb-6">My challenges</h1>
    <div class="space-y-3">
        @forelse($markets as $market)
            <a href="{{ route('betting.challenges.show', $market) }}" class="block p-4 rounded-xl border border-amber-900/25 bg-slate-900/40 hover:border-amber-700/40">
                <div class="flex justify-between">
                    <div>
                        <p class="font-medium text-white">{{ $market->title }}</p>
                        <p class="text-sm text-gray-500">{{ $market->event?->title }} · {{ str_replace('_', ' ', $market->status->value) }}</p>
                    </div>
                    <span class="text-amber-400">{{ number_format($market->stake_amount, 0) }} pts</span>
                </div>
            </a>
        @empty
            <p class="text-gray-500">No challenges yet.</p>
        @endforelse
    </div>
    {{ $markets->links() }}
</div>
@endsection
