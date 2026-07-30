@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold text-amber-400 font-serif mb-8">{{ __('betting.leaderboard') }}</h1>
    <div class="space-y-2">
        @forelse($rows as $row)
            <div class="flex items-center justify-between p-3 rounded-xl border border-amber-900/25 bg-slate-900/40">
                <div class="flex items-center gap-4">
                    <span class="text-amber-400 font-semibold w-8">#{{ $row->rank }}</span>
                    <div>
                        <p class="text-white font-medium">{{ $row->user->bettingProfile?->username ?? $row->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $row->wins }}W / {{ $row->losses }}L · {{ $row->settled_markets }} settled</p>
                    </div>
                </div>
                <span class="text-white font-semibold">{{ number_format($row->net_points, 0) }} pts</span>
            </div>
        @empty
            <p class="text-gray-500">Leaderboard will populate after settled challenges this week.</p>
        @endforelse
    </div>
</div>
@endsection
