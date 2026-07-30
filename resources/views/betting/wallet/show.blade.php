@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 mb-6">Play-money wallet</h1>
    <div class="p-6 rounded-xl border border-amber-900/30 bg-slate-900/50 space-y-4 mb-8">
        <p class="text-sm text-amber-500/90">Play money only — no cash value, no withdrawals.</p>
        <dl class="grid grid-cols-2 gap-4">
            <div><dt class="text-gray-500 text-sm">Available</dt><dd class="text-2xl font-bold text-white">{{ number_format($wallet->available, 0) }}</dd></div>
            <div><dt class="text-gray-500 text-sm">Locked in bets</dt><dd class="text-2xl font-bold text-amber-400">{{ number_format($wallet->locked, 0) }}</dd></div>
        </dl>
        @if($canClaimFaucet)
            <form method="POST" action="{{ route('betting.wallet.faucet') }}">@csrf
                <button class="mt-2 bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-4 py-2 rounded-lg text-sm">Claim daily faucet ({{ number_format(config('betting.faucet_points')) }} pts)</button>
            </form>
        @elseif($nextFaucetAt)
            <p class="text-xs text-gray-500 mt-2">Next faucet available {{ $nextFaucetAt->diffForHumans() }}</p>
        @endif
    </div>
    <h2 class="font-semibold text-white mb-3">Recent activity</h2>
    <ul class="text-sm space-y-2">
        @forelse($entries as $entry)
            <li class="flex justify-between border-b border-amber-900/15 pb-2">
                <span class="text-gray-400">{{ str_replace('_', ' ', $entry->type->value) }}</span>
                <span class="{{ in_array($entry->type->value, ['settlement_credit', 'grant', 'void_refund', 'stake_release']) ? 'text-emerald-400' : 'text-gray-300' }}">
                    {{ in_array($entry->type->value, ['stake_lock', 'settlement_debit']) ? '-' : '+' }}{{ number_format($entry->amount, 0) }}
                </span>
            </li>
        @empty
            <li class="text-gray-500">No transactions yet.</li>
        @endforelse
    </ul>
</div>
@endsection
