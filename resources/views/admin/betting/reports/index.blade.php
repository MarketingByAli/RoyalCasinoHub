@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-6">User reports</h1>
@forelse($reports as $report)
    <div class="p-4 mb-4 border border-amber-900/30 rounded-lg">
        <p class="text-white">{{ $report->reported->bettingProfile?->username ?? $report->reported->name }}</p>
        <p class="text-sm text-gray-500">{{ $report->reason }} · reported by {{ $report->reporter->name }}</p>
        <p class="text-sm text-gray-400 mt-2">{{ $report->explanation }}</p>
        <form method="POST" action="{{ route('admin.betting.reports.reviewed', $report) }}" class="mt-2">@csrf<button class="text-amber-400 text-sm">Mark reviewed</button></form>
    </div>
@empty
    <p class="text-gray-500">No open reports.</p>
@endforelse
{{ $reports->links() }}
@endsection
