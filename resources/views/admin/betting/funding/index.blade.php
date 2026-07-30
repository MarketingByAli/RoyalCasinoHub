@extends('layouts.admin')

@section('content')
<div class="max-w-5xl space-y-10">
    <h1 class="text-2xl font-bold text-amber-400">Deposits & withdrawals</h1>

    <section>
        <h2 class="text-lg font-semibold text-white mb-4">Pending / recent withdrawals</h2>
        <div class="space-y-3">
            @forelse($withdrawals as $w)
                <div class="p-4 rounded-lg border border-amber-900/30 bg-slate-900/40">
                    <div class="flex flex-wrap justify-between gap-2">
                        <div>
                            <p class="text-white">{{ $w->user->bettingProfile?->username ?? $w->user->email }} · {{ number_format($w->amount, 0) }} pts</p>
                            <p class="text-xs text-gray-500">{{ $w->coin_name }}{{ $w->network ? ' / '.$w->network : '' }} → {{ $w->destination_address }}</p>
                            <p class="text-xs text-amber-500 mt-1">Status: {{ $w->status }}</p>
                            @if($w->user_note)<p class="text-xs text-gray-400 mt-1">{{ $w->user_note }}</p>@endif
                        </div>
                        @if($w->status === 'pending')
                            <div class="space-y-2 min-w-[220px]">
                                <form method="POST" action="{{ route('admin.betting.funding.withdrawals.approve', $w) }}" class="space-y-2">
                                    @csrf
                                    <input name="admin_note" placeholder="Tx id / note" class="w-full bg-slate-800 border rounded px-2 py-1 text-sm">
                                    <button class="text-emerald-400 text-sm">Mark paid</button>
                                </form>
                                <form method="POST" action="{{ route('admin.betting.funding.withdrawals.reject', $w) }}">
                                    @csrf
                                    <button class="text-red-400 text-sm">Reject & return funds</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No withdrawals yet.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $withdrawals->links() }}</div>
    </section>

    <section>
        <h2 class="text-lg font-semibold text-white mb-4">Deposit notices</h2>
        <div class="space-y-3">
            @forelse($deposits as $d)
                <div class="p-4 rounded-lg border border-amber-900/30 bg-slate-900/40">
                    <div class="flex flex-wrap justify-between gap-2">
                        <div>
                            <p class="text-white">{{ $d->user->bettingProfile?->username ?? $d->user->email }}</p>
                            <p class="text-xs text-gray-500">{{ $d->method?->displayLabel() ?? 'Method' }} · claimed {{ $d->amount ? number_format($d->amount, 2) : '—' }}</p>
                            @if($d->tx_hash)<p class="text-xs text-gray-400 break-all">TX: {{ $d->tx_hash }}</p>@endif
                            <p class="text-xs text-amber-500 mt-1">Status: {{ $d->status }}</p>
                        </div>
                        @if($d->status === 'pending')
                            <div class="space-y-2 min-w-[220px]">
                                <form method="POST" action="{{ route('admin.betting.funding.deposits.credit', $d) }}" class="space-y-2">
                                    @csrf
                                    <input type="number" step="0.01" min="0.01" name="credited_amount" value="{{ $d->amount }}" required placeholder="Credit amount" class="w-full bg-slate-800 border rounded px-2 py-1 text-sm">
                                    <input name="admin_note" placeholder="Admin note" class="w-full bg-slate-800 border rounded px-2 py-1 text-sm">
                                    <button class="text-emerald-400 text-sm">Credit wallet</button>
                                </form>
                                <form method="POST" action="{{ route('admin.betting.funding.deposits.reject', $d) }}">
                                    @csrf
                                    <button class="text-red-400 text-sm">Reject</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No deposit notices yet.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $deposits->links() }}</div>
    </section>
</div>
@endsection
