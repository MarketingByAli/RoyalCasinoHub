@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">Activity log</h1>

<form method="GET" class="mb-6 flex gap-2">
    <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action" class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 max-w-xs">
    <button type="submit" class="px-4 py-2 bg-amber-500/20 text-amber-300 rounded-lg text-sm">Filter</button>
</form>

<div class="overflow-x-auto border border-amber-900/30 rounded-xl">
    <table class="min-w-full text-sm text-left">
        <thead>
            <tr class="border-b border-amber-900/30 text-gray-400">
                <th class="p-3">When</th>
                <th class="p-3">User</th>
                <th class="p-3">Action</th>
                <th class="p-3">Subject</th>
                <th class="p-3">IP</th>
            </tr>
        </thead>
        <tbody class="text-gray-300">
            @foreach($logs as $log)
                <tr class="border-b border-amber-900/10">
                    <td class="p-3 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td class="p-3">{{ $log->user?->email ?? '—' }}</td>
                    <td class="p-3">{{ $log->action }}</td>
                    <td class="p-3 text-xs">{{ $log->subject_type }} #{{ $log->subject_id }}</td>
                    <td class="p-3 text-xs">{{ $log->ip_address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $logs->withQueryString()->links() }}</div>
@endsection
