@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-2">{{ $market->title }}</h1>
<p class="text-gray-500 mb-6">{{ $market->status->value }} · {{ $market->event?->title }}</p>

<div class="grid md:grid-cols-2 gap-6 mb-8">
    <div class="p-4 border border-amber-900/30 rounded-lg">
        <h2 class="font-semibold mb-2">Details</h2>
        <p class="text-sm text-gray-400">{{ $market->description }}</p>
        @if($market->review_flags)<p class="text-amber-500 text-sm mt-2">Flags: {{ implode(', ', $market->review_flags) }}</p>@endif
        @if($market->currentVersion)<p class="text-xs text-gray-600 mt-2">Hash: {{ $market->currentVersion->terms_hash }}</p>@endif
    </div>
    <div class="p-4 border border-amber-900/30 rounded-lg space-y-2">
        @if($market->status->value === 'pending_review')
            <form method="POST" action="{{ route('admin.betting.markets.approve', $market) }}">@csrf<button class="bg-emerald-600 text-white px-3 py-1 rounded text-sm">Approve</button></form>
            <form method="POST" action="{{ route('admin.betting.markets.reject', $market) }}" class="space-y-2">@csrf<input name="reason" required placeholder="Rejection reason" class="w-full bg-slate-800 border rounded px-2 py-1 text-sm"><button class="text-red-400 text-sm">Reject</button></form>
        @endif
        <form method="POST" action="{{ route('admin.betting.markets.publish-result', $market) }}" class="flex gap-2">@csrf<input name="winning_outcome" placeholder="Outcome" class="bg-slate-800 border rounded px-2 py-1 text-sm flex-1"><button class="text-amber-400 text-sm">Publish result</button></form>
        <form method="POST" action="{{ route('admin.betting.markets.settle', $market) }}">@csrf<button class="text-amber-400 text-sm">Force settle</button></form>
        <form method="POST" action="{{ route('admin.betting.markets.void', $market) }}">@csrf<button class="text-red-400 text-sm">Void & refund</button></form>
    </div>
</div>

<h2 class="font-semibold mb-2">Audit log</h2>
<ul class="text-xs text-gray-500 space-y-1 max-h-64 overflow-y-auto">
    @foreach($auditLogs as $log)
        <li>{{ $log->created_at }} · {{ $log->previous_status }} → {{ $log->new_status }} · {{ $log->reason }}</li>
    @endforeach
</ul>
@endsection
