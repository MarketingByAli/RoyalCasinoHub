@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">Reviews</h1>

<form method="GET" class="mb-6">
    <select name="status" onchange="this.form.submit()" class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        <option value="">All</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
    </select>
</form>

@if(request('status') === 'pending' || request('status') === '')
    <div class="mb-6 flex flex-wrap gap-4">
        <form action="{{ route('admin.reviews.bulk-approve') }}" method="POST" id="bulk-approve" class="inline">
            @csrf
        </form>
        <form action="{{ route('admin.reviews.bulk-reject') }}" method="POST" id="bulk-reject" class="inline">
            @csrf
        </form>
        <button type="button" class="text-sm bg-emerald-500/20 text-emerald-300 px-4 py-2 rounded-lg" onclick="(function(){var f=document.getElementById('bulk-approve');f.querySelectorAll('input[name=\'review_ids[]\']').forEach(function(e){e.remove();});document.querySelectorAll('.review-check:checked').forEach(function(cb){var i=document.createElement('input');i.type='hidden';i.name='review_ids[]';i.value=cb.value;f.appendChild(i);});f.submit();})();">Bulk approve selected</button>
        <button type="button" class="text-sm bg-red-500/20 text-red-300 px-4 py-2 rounded-lg" onclick="(function(){var f=document.getElementById('bulk-reject');f.querySelectorAll('input[name=\'review_ids[]\']').forEach(function(e){e.remove();});document.querySelectorAll('.review-check:checked').forEach(function(cb){var i=document.createElement('input');i.type='hidden';i.name='review_ids[]';i.value=cb.value;f.appendChild(i);});f.submit();})();">Bulk reject selected</button>
    </div>
@endif

<div class="space-y-4">
    @foreach($reviews as $review)
        <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
            <div class="flex justify-between items-start gap-4">
                @if($review->status === 'pending')
                    <input type="checkbox" class="review-check mt-1 rounded border-amber-900/40" value="{{ $review->id }}" form="none">
                @endif
                <div class="flex-1 min-w-0">
                    <a href="{{ route('casino.show', $review->casino->slug) }}" class="text-amber-400 font-semibold">{{ $review->casino->name }}</a>
                    <h3 class="text-white mt-1">{{ $review->title }}</h3>
                    <p class="text-gray-400 mt-2">{{ Str::limit($review->content, 200) }}</p>
                    <p class="text-sm text-gray-500 mt-2">by {{ $review->user->name }} · {{ $review->created_at->format('M j, Y') }}</p>
                    <form action="{{ route('admin.reviews.note', $review) }}" method="POST" class="mt-4 space-y-2">
                        @csrf
                        @method('PUT')
                        <label class="text-xs text-gray-500">Internal note</label>
                        <textarea name="admin_internal_note" rows="2" class="w-full bg-slate-900/80 border border-amber-900/30 rounded-lg px-3 py-2 text-sm text-gray-300">{{ old('admin_internal_note', $review->admin_internal_note) }}</textarea>
                        <button type="submit" class="text-xs text-amber-400 hover:underline">Save note</button>
                    </form>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="text-amber-400">@for($i = 1; $i <= 5; $i++){{ $i <= $review->rating ? '★' : '☆' }}@endfor</span>
                    <span class="px-2 py-1 rounded text-sm {{ $review->status === 'approved' ? 'bg-emerald-500/20' : ($review->status === 'pending' ? 'bg-amber-500/20' : 'bg-red-500/20') }}">{{ $review->status }}</span>
                    @if($review->status === 'pending')
                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-emerald-400 hover:underline text-sm">Approve</button>
                        </form>
                        <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-red-400 hover:underline text-sm">Reject</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6">{{ $reviews->withQueryString()->links() }}</div>
@endsection
