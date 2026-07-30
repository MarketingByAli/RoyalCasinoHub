@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-amber-400 font-serif mb-2">{{ __('betting.explore_events') }}</h1>
    <div class="space-y-4 mt-8">
        @forelse($events as $event)
            <div class="p-4 rounded-xl border border-amber-900/25 bg-slate-900/40">
                <h2 class="font-semibold text-white">{{ $event->title }}</h2>
                <p class="text-sm text-gray-500">{{ $event->category }} · starts {{ $event->start_at->format('M j, Y H:i') }}</p>
            </div>
        @empty
            <p class="text-gray-500">No upcoming events.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $events->links() }}</div>
</div>
@endsection
