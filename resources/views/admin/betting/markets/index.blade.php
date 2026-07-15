@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-6">Markets</h1>
<form method="GET" class="mb-4 flex gap-2 items-center">
    <select name="status" class="bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
        <option value="">All statuses</option>
        @foreach(['pending_review','open','fully_matched','dispute_window','settled','rejected'] as $s)
            <option value="{{ $s }}" @selected(request('status', 'pending_review')===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="text-amber-400">Filter</button>
</form>
<table class="w-full text-sm">
    <thead><tr class="border-b border-amber-900/30 text-left"><th class="py-2">Title</th><th>Status</th><th>Stake</th><th></th></tr></thead>
    <tbody>
        @foreach($markets as $market)
            <tr class="border-b border-amber-900/20">
                <td class="py-2">{{ $market->title }}</td>
                <td>{{ $market->status->value }}</td>
                <td>{{ number_format($market->stake_amount, 0) }}</td>
                <td><a href="{{ route('admin.betting.markets.show', $market) }}" class="text-amber-400">Review</a></td>
            </tr>
        @endforeach
    </tbody>
</table>
{{ $markets->links() }}
@endsection
