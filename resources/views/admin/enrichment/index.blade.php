@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">Enrichment Queue</h1>

<div class="grid grid-cols-4 gap-6 mb-8">
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
        <p class="text-gray-400 text-sm">Pending</p>
        <p class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['pending'] }}</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
        <p class="text-gray-400 text-sm">Processing</p>
        <p class="text-2xl font-bold text-blue-400 mt-1">{{ $stats['processing'] }}</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
        <p class="text-gray-400 text-sm">Done</p>
        <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['done'] }}</p>
    </div>
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
        <p class="text-gray-400 text-sm">Failed</p>
        <p class="text-2xl font-bold text-red-400 mt-1">{{ $stats['failed'] }}</p>
    </div>
</div>

<p class="text-gray-400 mb-4">Run <code class="bg-slate-800 px-2 py-1 rounded">php artisan enrichment:process</code> to process the queue. Scheduled every 5 minutes.</p>

<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-amber-900/30">
                <th class="text-left py-3 px-4">Casino</th>
                <th class="text-left py-3 px-4">Job Type</th>
                <th class="text-left py-3 px-4">Status</th>
                <th class="text-left py-3 px-4">Attempts</th>
                <th class="text-left py-3 px-4">Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentJobs as $job)
                <tr class="border-b border-amber-900/20">
                    <td class="py-3 px-4">
                        <a href="{{ route('admin.casinos.edit', $job->casino) }}" class="text-amber-400 hover:underline">{{ $job->casino->name }}</a>
                    </td>
                    <td class="py-3 px-4 text-gray-400">{{ $job->job_type }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-sm {{ $job->status === 'done' ? 'bg-emerald-500/20' : ($job->status === 'failed' ? 'bg-red-500/20' : 'bg-amber-500/20') }}">{{ $job->status }}</span>
                    </td>
                    <td class="py-3 px-4">{{ $job->attempts }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ Str::limit($job->result, 50) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
