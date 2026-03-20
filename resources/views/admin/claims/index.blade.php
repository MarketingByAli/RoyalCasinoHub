@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">Claimed Listings</h1>

<form method="GET" class="mb-6">
    <select name="status" onchange="this.form.submit()" class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        <option value="">All</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
    </select>
</form>

<div class="space-y-4">
    @foreach($claims as $claim)
        <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <a href="{{ route('casino.show', $claim->casino->slug) }}" class="text-amber-400 font-semibold">{{ $claim->casino->name }}</a>
                    <p class="text-gray-400 mt-1">Claimed by {{ $claim->user->name }} ({{ $claim->user->email }})</p>
                    <p class="text-sm text-gray-500 mt-1">Submitted {{ $claim->submitted_at?->format('M j, Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded text-sm {{ $claim->status === 'approved' ? 'bg-emerald-500/20' : ($claim->status === 'pending' ? 'bg-amber-500/20' : 'bg-red-500/20') }}">{{ $claim->status }}</span>
                    @if($claim->status === 'pending')
                        <form action="{{ route('admin.claims.approve', $claim) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-emerald-400 hover:underline text-sm">Approve</button>
                        </form>
                        <form action="{{ route('admin.claims.reject', $claim) }}" method="POST" class="inline">
                            @csrf
                            <input type="text" name="notes" placeholder="Rejection reason" class="bg-slate-800 border border-amber-900/30 rounded px-2 py-1 text-sm w-40">
                            <button type="submit" class="text-red-400 hover:underline text-sm">Reject</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6">{{ $claims->withQueryString()->links() }}</div>
@endsection
