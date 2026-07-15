@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-amber-400">Betting events</h1>
    <a href="{{ route('admin.betting.events.create') }}" class="bg-amber-500 text-amber-950 px-4 py-2 rounded-lg font-semibold">Add event</a>
</div>
<table class="w-full">
    <thead><tr class="border-b border-amber-900/30 text-left"><th class="py-2">Title</th><th>Start</th><th>Status</th><th>Markets</th><th></th></tr></thead>
    <tbody>
        @foreach($events as $event)
            <tr class="border-b border-amber-900/20">
                <td class="py-3">{{ $event->title }}</td>
                <td class="text-gray-400">{{ $event->start_at->format('Y-m-d H:i') }}</td>
                <td>{{ $event->status }}</td>
                <td>{{ $event->markets_count }}</td>
                <td><a href="{{ route('admin.betting.events.edit', $event) }}" class="text-amber-400 text-sm">Edit</a></td>
            </tr>
        @endforeach
    </tbody>
</table>
{{ $events->links() }}
@endsection
