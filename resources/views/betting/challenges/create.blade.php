@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 mb-6">Create friend challenge</h1>
    <form method="POST" action="{{ route('betting.challenges.store') }}" class="space-y-4 bg-slate-900/50 border border-amber-900/25 rounded-xl p-6" x-data="{ format: '{{ old('format', 'yes_no') }}' }">
        @csrf
        <div>
            <label class="block text-sm text-gray-300 mb-1">Event</label>
            <select name="betting_event_id" required class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
                @foreach($events as $event)
                    <option value="{{ $event->id }}" @selected(old('betting_event_id') == $event->id)>{{ $event->title }} ({{ $event->start_at->format('M j, H:i') }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="255" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Format</label>
            <select name="format" x-model="format" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
                <option value="yes_no">Yes / No</option>
                <option value="team_vs_team">Team A vs Team B</option>
            </select>
        </div>
        <div x-show="format === 'team_vs_team'" class="grid grid-cols-2 gap-4">
            <input type="text" name="team_a" value="{{ old('team_a', 'Team A') }}" placeholder="Team A" class="bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
            <input type="text" name="team_b" value="{{ old('team_b', 'Team B') }}" placeholder="Team B" class="bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Your outcome (what you bet on)</label>
            <input type="text" name="creator_outcome" value="{{ old('creator_outcome') }}" required placeholder="Yes, No, or team name" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Stake (play points)</label>
            <input type="number" name="stake_amount" value="{{ old('stake_amount', 100) }}" required min="1" max="{{ config('betting.max_stake_per_market') }}" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold py-3 rounded-xl">Create & share</button>
    </form>
</div>
@endsection
