@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex flex-wrap justify-between items-end gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-amber-400 font-serif">My challenges</h1>
            <p class="text-sm text-gray-500 mt-1">All challenges you created or joined.</p>
        </div>
        <a href="{{ route('betting.challenges.create') }}" class="text-sm text-amber-400 hover:text-amber-300">New challenge</a>
    </div>
    <div class="space-y-3">
        @forelse($markets as $market)
            <a href="{{ route('betting.challenges.show', $market) }}" class="block p-4 rounded-xl border border-amber-900/25 bg-slate-900/40 hover:border-amber-700/40">
                <div class="flex justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-medium text-white truncate">{{ $market->title }}</p>
                        <p class="text-sm text-gray-500">{{ $market->event?->title }} · {{ ucfirst(str_replace('_', ' ', $market->status->value)) }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-amber-400 font-semibold tabular-nums">{{ number_format($market->stake_amount, 0) }}</span>
                        <p class="text-xs text-gray-600">credits</p>
                    </div>
                </div>
            </a>
        @empty
            <p class="text-gray-500">No challenges yet.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $markets->links() }}</div>
</div>
@endsection

