@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-10">
    <div>
        <h1 class="text-3xl font-bold text-amber-400 font-serif">Wallet</h1>
        <p class="text-gray-500 text-sm mt-1">Available balance and crypto deposit / withdraw options.</p>
    </div>

    @if(session('success'))
        <div class="p-3 rounded-lg border border-emerald-800/50 bg-emerald-950/30 text-emerald-300 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 rounded-lg border border-red-800/50 bg-red-950/30 text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    <div class="p-6 rounded-xl border border-amber-900/30 bg-slate-900/50">
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-gray-500 text-sm">Available</dt>
                <dd class="text-3xl font-bold text-white">{{ number_format($wallet->available, 0) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 text-sm">Locked</dt>
                <dd class="text-3xl font-bold text-amber-400">{{ number_format($wallet->locked, 0) }}</dd>
            </div>
        </dl>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="#add-funds" class="inline-block bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-5 py-2.5 rounded-xl">Add funds</a>
            <a href="#withdraw" class="inline-block border border-amber-700 text-amber-400 hover:bg-amber-500/10 font-semibold px-5 py-2.5 rounded-xl">Withdraw</a>
            <a href="#activity" class="inline-block text-gray-400 hover:text-white text-sm px-3 py-2">Activity</a>
        </div>
    </div>

    <section id="add-funds" class="space-y-6 scroll-mt-24">
        <h2 class="text-xl font-semibold text-white">Add funds</h2>
        <p class="text-sm text-gray-400">Send crypto to one of the addresses below, then submit a deposit notice so we can credit your balance after confirmation.</p>

        @forelse($depositMethods as $method)
            <div class="p-5 rounded-xl border border-amber-900/25 bg-slate-900/40 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-amber-400">{{ $method->displayLabel() }}</h3>
                        @if($method->instructions)
                            <p class="text-sm text-gray-400 mt-2 whitespace-pre-line">{{ $method->instructions }}</p>
                        @endif
                    </div>
                    @if($method->qrUrl())
                        <img src="{{ $method->qrUrl() }}" alt="{{ $method->coin_name }} QR code" class="w-36 h-36 rounded-lg border border-amber-900/40 bg-white p-2 object-contain">
                    @endif
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Deposit address</label>
                    <div class="flex gap-2">
                        <input readonly value="{{ $method->address }}" id="deposit-address-{{ $method->id }}" class="flex-1 text-sm bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-gray-200" onclick="this.select()">
                        <button type="button" class="text-xs text-amber-400 border border-amber-800 rounded-lg px-3"
                            onclick="navigator.clipboard.writeText(document.getElementById('deposit-address-{{ $method->id }}').value)">Copy</button>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No deposit methods are configured yet. Please check back soon.</p>
        @endforelse

        @if($depositMethods->isNotEmpty())
            <form method="POST" action="{{ route('betting.wallet.deposit-notice') }}" class="p-5 rounded-xl border border-amber-900/25 bg-slate-900/40 space-y-4">
                @csrf
                <h3 class="font-semibold text-white">I already sent a deposit</h3>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Payment method</label>
                    <select name="deposit_method_id" required class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">
                        @foreach($depositMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->displayLabel() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Amount sent</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Transaction hash / ID</label>
                        <input type="text" name="tx_hash" value="{{ old('tx_hash') }}" class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Note (optional)</label>
                    <textarea name="user_note" rows="2" class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">{{ old('user_note') }}</textarea>
                </div>
                <button class="bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-5 py-2.5 rounded-xl">Submit deposit notice</button>
            </form>
        @endif

        @if($recentNotices->isNotEmpty())
            <div>
                <h3 class="font-semibold text-white mb-2">Your recent deposit notices</h3>
                <ul class="text-sm space-y-2">
                    @foreach($recentNotices as $notice)
                        <li class="flex justify-between border-b border-amber-900/15 pb-2 text-gray-400">
                            <span>{{ $notice->method?->displayLabel() ?? 'Deposit' }} · {{ $notice->status }}</span>
                            <span>{{ $notice->amount ? number_format($notice->amount, 2) : '—' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    <section id="withdraw" class="space-y-6 scroll-mt-24">
        <h2 class="text-xl font-semibold text-white">Withdraw</h2>
        <p class="text-sm text-gray-400">Funds are reserved from your available balance until the withdrawal is paid or rejected.</p>

        <form method="POST" action="{{ route('betting.wallet.withdraw') }}" class="p-5 rounded-xl border border-amber-900/25 bg-slate-900/40 space-y-4">
            @csrf
            @if($depositMethods->isNotEmpty())
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Coin / network</label>
                    <select name="deposit_method_id" class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">
                        <option value="">Custom</option>
                        @foreach($depositMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->displayLabel() }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Coin name</label>
                    <input type="text" name="coin_name" value="{{ old('coin_name') }}" placeholder="USDT" class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Network</label>
                    <input type="text" name="network" value="{{ old('network') }}" placeholder="TRC20" class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Your wallet address</label>
                <input type="text" name="destination_address" value="{{ old('destination_address') }}" required class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Amount</label>
                <input type="number" name="amount" value="{{ old('amount') }}" required min="1" step="1" max="{{ max(1, (int) $wallet->available) }}" class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Note (optional)</label>
                <textarea name="user_note" rows="2" class="w-full bg-slate-950 border border-amber-900/30 rounded-lg px-3 py-2 text-white">{{ old('user_note') }}</textarea>
            </div>
            <button class="border border-amber-700 text-amber-400 hover:bg-amber-500/10 font-semibold px-5 py-2.5 rounded-xl">Request withdrawal</button>
        </form>

        @if($pendingWithdrawals->isNotEmpty())
            <div>
                <h3 class="font-semibold text-white mb-2">Pending withdrawals</h3>
                <ul class="text-sm space-y-2">
                    @foreach($pendingWithdrawals as $w)
                        <li class="flex justify-between border-b border-amber-900/15 pb-2 text-gray-400">
                            <span>{{ $w->coin_name }}{{ $w->network ? ' / '.$w->network : '' }} → {{ \Illuminate\Support\Str::limit($w->destination_address, 18) }}</span>
                            <span class="text-amber-400">{{ number_format($w->amount, 0) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    <section id="activity" class="space-y-3 scroll-mt-24">
        <h2 class="font-semibold text-white mb-3">Recent activity</h2>
        <ul class="text-sm space-y-2">
            @forelse($entries as $entry)
                <li class="flex justify-between border-b border-amber-900/15 pb-2">
                    <span class="text-gray-400">{{ str_replace('_', ' ', $entry->type->value) }}</span>
                    <span class="{{ in_array($entry->type->value, ['settlement_credit', 'grant', 'void_refund', 'stake_release', 'deposit', 'referral_bonus', 'withdrawal_release'], true) ? 'text-emerald-400' : 'text-gray-300' }}">
                        {{ in_array($entry->type->value, ['stake_lock', 'settlement_debit', 'withdrawal'], true) ? '-' : '+' }}{{ number_format($entry->amount, 0) }}
                    </span>
                </li>
            @empty
                <li class="text-gray-500">No transactions yet.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
