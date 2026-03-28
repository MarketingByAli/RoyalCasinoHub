@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">Casino reports</h1>

<form method="GET" class="mb-6">
    <select name="status" onchange="this.form.submit()" class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        <option value="">All</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
        <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
    </select>
</form>

<div class="space-y-4">
    @foreach($reports as $report)
        <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
            <div class="flex justify-between gap-4">
                <div>
                    <a href="{{ route('admin.casinos.edit', $report->casino) }}" class="text-amber-400 font-semibold">{{ $report->casino->name }}</a>
                    <p class="text-sm text-gray-500 mt-1">by {{ $report->user->email }} · {{ $report->reason }}</p>
                    @if($report->details)
                        <p class="text-gray-400 mt-2">{{ $report->details }}</p>
                    @endif
                </div>
                <form action="{{ route('admin.casino-reports.update', $report) }}" method="POST" class="flex items-start gap-2">
                    @csrf
                    <select name="status" class="bg-slate-900 border border-amber-900/30 rounded-lg px-2 py-1 text-sm">
                        <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>pending</option>
                        <option value="reviewed" {{ $report->status === 'reviewed' ? 'selected' : '' }}>reviewed</option>
                        <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>dismissed</option>
                    </select>
                    <button type="submit" class="text-sm text-amber-400 hover:underline">Save</button>
                </form>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6">{{ $reports->withQueryString()->links() }}</div>
@endsection
