@extends('layouts.app')

@section('content')
@php
    $inviteUrl = route('betting.invite.show', $market->invite_token);
    $userId = auth()->id();
    $isParticipant = auth()->check() && $market->participants->contains(fn ($p) => $p->user_id === $userId && $p->status->value !== 'withdrawn');
    $fee = (float) $market->platform_fee_percent;
    $joinable = in_array($market->status->value, ['open', 'partially_matched'], true);
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
                <div class="flex justify-between"><dt class="text-gray-500">Seats</dt><dd>{{ $market->participant_cap }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Visibility</dt><dd>{{ $market->visibility }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Platform fee</dt><dd>{{ number_format($fee, 1) }}%</dd></div>
            </dl>
            @if($market->currentVersion)
                <p class="text-xs text-gray-600">Locked terms v{{ $market->currentVersion->version }} · {{ Str::limit($market->currentVersion->terms_hash, 16) }}</p>
            @endif
        </div>
        <div class="p-5 rounded-xl border border-amber-900/25 bg-slate-900/50">
            @if($joinable && auth()->id() === $market->creator_id)
                <h2 class="font-semibold text-white mb-3">Share invite</h2>
                <input readonly value="{{ $inviteUrl }}" class="w-full text-xs bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-gray-300 mb-3" onclick="this.select()">
                <p class="text-xs text-gray-500">Expires {{ $market->expires_at?->diffForHumans() }}.</p>
                @foreach($market->participants->filter(fn ($p) => $p->status->value === 'pending_counter') as $pending)
                    <div class="mt-3 p-3 border border-amber-900/30 rounded-lg text-sm">
                        <p class="text-gray-300">Counter from {{ $pending->user->bettingProfile?->username ?? $pending->user->name }}: {{ number_format($pending->proposed_stake_amount, 0) }} pts on {{ $pending->proposed_outcome }}</p>
                        <div class="flex gap-3 mt-2">
                            <form method="POST" action="{{ route('betting.challenges.counter.accept', [$market, $pending->user]) }}">@csrf<button class="text-emerald-400 text-xs">Accept</button></form>
                            <form method="POST" action="{{ route('betting.challenges.counter.reject', [$market, $pending->user]) }}">@csrf<button class="text-red-400 text-xs">Reject</button></form>
                        </div>
                    </div>
                @endforeach
                <form action="{{ route('betting.challenges.cancel', $market) }}" method="POST" class="mt-4">@csrf<button type="submit" class="text-red-400 text-sm hover:underline">Cancel challenge</button></form>
            @elseif($joinable && auth()->check() && auth()->id() !== $market->creator_id && ! $isParticipant)
                <h2 class="font-semibold text-white mb-3">Join challenge</h2>
                @if($wallet)<p class="text-xs text-gray-500 mb-4">Your balance: {{ number_format($wallet->available, 0) }} available</p>@endif
                <form action="{{ route('betting.challenges.join', $market) }}" method="POST" class="space-y-3 mb-2">
                    @csrf
                    @if(!empty($inviteToken))
                        <input type="hidden" name="invite_token" value="{{ $inviteToken }}">
                    @endif
                    <select name="outcome" required class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-sm text-white">
                        @foreach($market->outcome_options as $option)
                            <option value="{{ $option }}" @selected($option === $market->challengerOutcome())>{{ $option }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="proposed_stake_amount" min="1" max="{{ config('betting.max_stake_per_market') }}" placeholder="Optional counter-offer stake" class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-sm text-white">
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold py-2 rounded-lg">Join & lock stake</button>
                </form>
                @if($market->visibility === 'private_invite' && empty($inviteToken))
                    <p class="text-xs text-red-400">Open the private invite link first to unlock join.</p>
                @endif
            @elseif($isParticipant)
                <h2 class="font-semibold text-white mb-2">Participants</h2>
                @foreach($market->participants as $p)
                    <p class="text-sm text-gray-300">{{ $p->role }} ({{ $p->status->value }}): {{ $p->user->bettingProfile?->username ?? $p->user->name }} → {{ $p->outcome }} ({{ number_format($p->stake_amount, 0) }} pts)</p>
                @endforeach
                @if($joinable && auth()->id() !== $market->creator_id)
                    <form action="{{ route('betting.challenges.withdraw', $market) }}" method="POST" class="mt-3">@csrf<button class="text-red-400 text-sm">Withdraw</button></form>
                @endif
                @if($market->winning_outcome)
                    <p class="mt-3 text-sm">Result: <span class="text-amber-400">{{ $market->winning_outcome }}</span></p>
                @endif
                @if($market->status->value === 'dispute_window')
                    <form action="{{ route('betting.disputes.store', $market) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-2 border-t border-amber-900/20 pt-4">
                        @csrf
                        <p class="text-sm text-gray-400">Dispute window open until {{ $market->dispute_window_ends_at?->format('M j, H:i') }}</p>
                        <select name="reason_category" required class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-sm text-white">
                            <option value="wrong_result">Wrong result</option>
                            <option value="event_cancelled">Event cancelled</option>
                            <option value="other">Other</option>
                        </select>
                        <textarea name="explanation" placeholder="Explain..." class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-sm text-white" rows="2"></textarea>
                        <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="text-xs text-gray-400">
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
