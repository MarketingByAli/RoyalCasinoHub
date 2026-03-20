@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-amber-400">Casinos</h1>
</div>

<form method="GET" class="flex gap-4 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    <select name="status" class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        <option value="">All Status</option>
        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
    </select>
    <select name="enrichment" class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        <option value="">All Enrichment</option>
        <option value="pending" {{ request('enrichment') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="done" {{ request('enrichment') === 'done' ? 'selected' : '' }}>Done</option>
    </select>
    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 px-4 py-2 rounded-lg">Filter</button>
</form>

<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-amber-900/30">
                <th class="text-left py-3 px-4">Name</th>
                <th class="text-left py-3 px-4">Country</th>
                <th class="text-left py-3 px-4">Status</th>
                <th class="text-left py-3 px-4">Enrichment</th>
                <th class="text-left py-3 px-4">Reviews</th>
                <th class="text-left py-3 px-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($casinos as $casino)
                <tr class="border-b border-amber-900/20 hover:bg-slate-800/30">
                    <td class="py-3 px-4">
                        <a href="{{ route('casino.show', $casino->slug) }}" class="text-amber-400 hover:underline" target="_blank" rel="noopener">{{ $casino->name }}</a>
                    </td>
                    <td class="py-3 px-4 text-gray-400">{{ $casino->country }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-sm {{ $casino->status === 'published' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-gray-500/20 text-gray-400' }}">{{ $casino->status }}</span>
                    </td>
                    <td class="py-3 px-4 text-gray-400">{{ $casino->enrichment_status }}</td>
                    <td class="py-3 px-4">{{ $casino->approved_reviews_count }}</td>
                    <td class="py-3 px-4 flex gap-2">
                        <a href="{{ route('admin.casinos.edit', $casino) }}" class="text-amber-400 hover:underline text-sm">Edit</a>
                        <form action="{{ route('admin.casinos.toggle-status', $casino) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-amber-400 hover:underline text-sm">{{ $casino->status === 'published' ? 'Unpublish' : 'Publish' }}</button>
                        </form>
                        <form action="{{ route('admin.casinos.queue-enrichment', $casino) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-amber-400 hover:underline text-sm">Re-enrich</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $casinos->withQueryString()->links() }}</div>
@endsection
