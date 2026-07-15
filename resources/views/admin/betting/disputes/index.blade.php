@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-6">Open disputes</h1>
@forelse($disputes as $dispute)
    <div class="p-4 mb-4 border border-amber-900/30 rounded-lg">
        <p class="font-medium">{{ $dispute->market->title }}</p>
        <p class="text-sm text-gray-500">{{ $dispute->reason_category }} · by {{ $dispute->user->name }}</p>
        <p class="text-sm text-gray-400 mt-2">{{ $dispute->explanation }}</p>
        <form method="POST" action="{{ route('admin.betting.disputes.resolve', $dispute) }}" class="mt-3 flex gap-2 items-center">
            @csrf
            <select name="resolution" class="bg-slate-800 border rounded px-2 py-1 text-sm"><option value="confirm">Confirm settlement</option><option value="void">Void & refund</option></select>
            <input name="note" placeholder="Note" class="bg-slate-800 border rounded px-2 py-1 text-sm flex-1">
            <button class="text-amber-400 text-sm">Resolve</button>
        </form>
    </div>
@empty
    <p class="text-gray-500">No open disputes.</p>
@endforelse
{{ $disputes->links() }}
@endsection
