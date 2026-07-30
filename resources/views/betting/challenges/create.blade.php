@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 mb-6">Create friend challenge</h1>
    @if($events->isEmpty())
        <p class="text-gray-500">No events available yet. Check back after admin adds approved events.</p>
    @else
    <form method="POST" action="{{ route('betting.challenges.store') }}" class="space-y-4 bg-slate-900/50 border border-amber-900/25 rounded-xl p-6"
        x-data="{ format: '{{ old('format', 'yes_no') }}', teamA: '{{ old('team_a', 'Team A') }}', teamB: '{{ old('team_b', 'Team B') }}' }">
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
            <input type="text" name="team_a" x-model="teamA" placeholder="Team A" class="bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
            <input type="text" name="team_b" x-model="teamB" placeholder="Team B" class="bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Your outcome</label>
            <template x-if="format === 'yes_no'">
                <select name="creator_outcome" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
                    <option value="Yes" @selected(old('creator_outcome') === 'Yes')>Yes</option>
                    <option value="No" @selected(old('creator_outcome') === 'No')>No</option>
                </select>
            </template>
            <template x-if="format === 'team_vs_team'">
                <select name="creator_outcome" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
                    <option :value="teamA" x-text="teamA"></option>
                    <option :value="teamB" x-text="teamB"></option>
                </select>
            </template>
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Stake (play points)</label>
            <input type="number" name="stake_amount" value="{{ old('stake_amount', 100) }}" required min="1" max="{{ config('betting.max_stake_per_market') }}" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Seats (2 = 1v1, up to {{ config('betting.max_participant_cap', 20) }} for pools)</label>
            <input type="number" name="participant_cap" value="{{ old('participant_cap', 2) }}" required min="2" max="{{ config('betting.max_participant_cap', 20) }}" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Visibility</label>
            <select name="visibility" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
                <option value="private_invite" @selected(old('visibility', 'private_invite') === 'private_invite')>Private invite only</option>
                <option value="public" @selected(old('visibility') === 'public')>Public on Explore</option>
            </select>
        </div>
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold py-3 rounded-xl">Create & share</button>
    </form>
    @endif
</div>
@endsection
