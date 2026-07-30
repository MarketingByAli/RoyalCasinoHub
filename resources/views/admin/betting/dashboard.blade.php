@extends('layouts.admin')

@section('content')
<div class="max-w-5xl">
    <h1 class="text-2xl font-bold text-amber-400 mb-6">Betting dashboard</h1>

    @if($mismatchCount > 0)
        <div class="mb-6 p-4 rounded-lg border border-red-800 bg-red-950/40 text-red-300">
            Wallet reconciliation found {{ $mismatchCount }} mismatch(es). Run <code>betting:reconcile-wallets</code>.
        </div>
    @endif

    <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <div class="p-4 rounded-xl border border-amber-900/30 bg-slate-900/50"><p class="text-gray-500 text-sm">Pending review</p><p class="text-2xl text-white">{{ $kpis['pending_review'] }}</p></div>
        <div class="p-4 rounded-xl border border-amber-900/30 bg-slate-900/50"><p class="text-gray-500 text-sm">Open / partial</p><p class="text-2xl text-white">{{ $kpis['open'] }} / {{ $kpis['partially_matched'] }}</p></div>
        <div class="p-4 rounded-xl border border-amber-900/30 bg-slate-900/50"><p class="text-gray-500 text-sm">Open disputes</p><p class="text-2xl text-white">{{ $kpis['open_disputes'] }}</p></div>
        <div class="p-4 rounded-xl border border-amber-900/30 bg-slate-900/50"><p class="text-gray-500 text-sm">Stuck markets</p><p class="text-2xl text-white">{{ $kpis['stuck_markets'] }}</p></div>
        <div class="p-4 rounded-xl border border-amber-900/30 bg-slate-900/50"><p class="text-gray-500 text-sm">Locked exposure</p><p class="text-2xl text-white">{{ number_format($kpis['total_locked'], 0) }}</p></div>
        <div class="p-4 rounded-xl border border-amber-900/30 bg-slate-900/50"><p class="text-gray-500 text-sm">Available points</p><p class="text-2xl text-white">{{ number_format($kpis['total_available'], 0) }}</p></div>
    </div>

    <form method="GET" class="mb-6 flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search username or email" class="flex-1 bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
        <button class="bg-amber-500 text-amber-950 font-semibold px-4 rounded">Search</button>
    </form>

    @if($users->isNotEmpty())
        <div class="space-y-2 mb-8">
            @foreach($users as $user)
                <div class="p-3 rounded border border-amber-900/25 flex justify-between">
                    <div>
                        <p class="text-white">{{ $user->bettingProfile?->username ?? $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->email }} · {{ $user->bettingProfile?->account_state?->value ?? 'no profile' }}</p>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <a href="{{ route('admin.betting.accounts.edit', $user) }}" class="text-amber-400">Account</a>
                        <a href="{{ route('admin.betting.wallets.index') }}?user_id={{ $user->id }}" class="text-amber-400">Wallet</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
