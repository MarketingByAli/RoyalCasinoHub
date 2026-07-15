@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-amber-400">Notifications</h1>
        @if($notifications->whereNull('read_at')->count())
            <form method="POST" action="{{ route('betting.notifications.read-all') }}">@csrf<button class="text-sm text-amber-400 hover:underline">Mark all read</button></form>
        @endif
    </div>
    <div class="space-y-2">
        @forelse($notifications as $n)
            <div class="p-4 rounded-xl border {{ $n->read_at ? 'border-amber-900/15 bg-slate-900/30' : 'border-amber-700/40 bg-slate-900/50' }}">
                <p class="text-sm text-white">{{ str_replace('_', ' ', $n->type) }} — {{ $n->data['market_title'] ?? '' }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                @if(!empty($n->data['market_id']))
                    <a href="{{ route('betting.challenges.show', $n->data['market_id']) }}" class="text-xs text-amber-400 hover:underline">View</a>
                @endif
            </div>
        @empty
            <p class="text-gray-500">No notifications yet.</p>
        @endforelse
    </div>
    {{ $notifications->links() }}
</div>
@endsection
