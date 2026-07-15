@extends('layouts.app')

@section('content')
@php
    $inviteUrl = route('betting.invite.show', $market->invite_token);
    $isParticipant = auth()->check() && in_array(auth()->id(), [$market->creator_id, $market->challenger_id]);
@endphp
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <span class="text-xs uppercase tracking-wider text-amber-500/80">{{ str_replace('_', ' ', $market->status->value) }}</span>
        <h1 class="text-3xl font-bold text-amber-400 font-serif mt-1">{{ $market->title }}</h1>
        <p class="text-gray-500 mt-2">{{ $market->event?->title }} · {{ $market->event?->start_at?->format('M j, Y H:i') }}</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-8">
        <div class="p-5 rounded-xl border border-amber-900/25 bg-slate-900/50 space-y-3">
            <h2 class="font-semibold text-white">Terms</h2>
            <p class="text-sm text-gray-400">{{ $market->description ?: 'No additional description.' }}</p>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-500">Format</dt><dd>{{ str_replace('_', ' ', $market->format->value) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Outcomes</dt><dd>{{ implode(' vs ', $market->outcome_options) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Creator picks</dt><dd class="text-amber-400">{{ $market->creator_outcome }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Stake each</dt><dd>{{ number_format($market->stake_amount, 0) }} pts</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Potential return</dt><dd>{{ number_format($market->stake_amount * 2, 0) }} pts (0% fee)</dd></div>
            </dl>
            @if($market->currentVersion)
                <p class="text-xs text-gray-600">Locked terms v{{ $market->currentVersion->version }} · {{ Str::limit($market->currentVersion->terms_hash, 16) }}</p>
            @endif
        </div>
        <div class="p-5 rounded-xl border border-amber-900/25 bg-slate-900/50">
            @if($market->status->value === 'open' && auth()->id() === $market->creator_id)
                <h2 class="font-semibold text-white mb-3">Share invite</h2>
                <input readonly value="{{ $inviteUrl }}" class="w-full text-xs bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-gray-300 mb-3" onclick="this.select()">
                <p class="text-xs text-gray-500">Send via WhatsApp, email, or any messenger. Expires {{ $market->expires_at?->diffForHumans() }}.</p>
                <form action="{{ route('betting.challenges.cancel', $market) }}" method="POST" class="mt-4">@csrf<button type="submit" class="text-red-400 text-sm hover:underline">Cancel challenge</button></form>
            @elseif($market->status->value === 'open' && auth()->check() && auth()->id() !== $market->creator_id)
                <h2 class="font-semibold text-white mb-3">Accept challenge?</h2>
                <p class="text-sm text-gray-400 mb-4">You will bet on <strong class="text-white">{{ $market->challengerOutcome() }}</strong> for {{ number_format($market->stake_amount, 0) }} pts.</p>
                @if($wallet)<p class="text-xs text-gray-500 mb-4">Your balance: {{ number_format($wallet->available, 0) }} available</p>@endif
                <form action="{{ route('betting.challenges.accept', $market) }}" method="POST" class="mb-2">@csrf<button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold py-2 rounded-lg">Accept & lock stake</button></form>
            @elseif($isParticipant)
                <h2 class="font-semibold text-white mb-2">Participants</h2>
                @foreach($market->participants as $p)
                    <p class="text-sm text-gray-300">{{ $p->role }}: {{ $p->user->bettingProfile?->username ?? $p->user->name }} → {{ $p->outcome }} ({{ number_format($p->stake_amount, 0) }} pts)</p>
                @endforeach
                @if($market->winning_outcome)
                    <p class="mt-3 text-sm">Result: <span class="text-amber-400">{{ $market->winning_outcome }}</span></p>
                @endif
                @if($market->status->value === 'dispute_window')
                    <form action="{{ route('betting.disputes.store', $market) }}" method="POST" class="mt-4 space-y-2 border-t border-amber-900/20 pt-4">
                        @csrf
                        <p class="text-sm text-gray-400">Dispute window open until {{ $market->dispute_window_ends_at?->format('M j, H:i') }}</p>
                        <select name="reason_category" required class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-sm text-white">
                            <option value="wrong_result">Wrong result</option>
                            <option value="event_cancelled">Event cancelled</option>
                            <option value="other">Other</option>
                        </select>
                        <textarea name="explanation" placeholder="Explain..." class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-sm text-white" rows="2"></textarea>
                        <button type="submit" class="text-amber-400 text-sm hover:underline">Dispute result</button>
                    </form>
                @endif
            @else
                <p class="text-gray-500 text-sm">This challenge is {{ $market->status->value }}.</p>
            @endif
        </div>
    </div>
    <a href="{{ route('betting.challenges.index') }}" class="text-amber-400 hover:underline text-sm">← Back to my challenges</a>
</div>
@endsection
