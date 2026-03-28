@extends('layouts.app')

@section('content')
<div class="max-w-3xl">
    <nav class="mb-6 text-sm text-gray-500">
        <a href="{{ route('account.index') }}" class="hover:text-amber-400">Account</a>
        <span class="mx-2">/</span>
        <span class="text-gray-400">My reviews</span>
    </nav>
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-6">My reviews</h1>
    @forelse($reviews as $review)
        <div class="bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 mb-4">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <a href="{{ route('casino.show', $review->casino->slug) }}" class="font-semibold text-white hover:text-amber-400">{{ $review->casino->name }}</a>
                    <p class="text-sm text-gray-500 mt-1">{{ $review->title }}</p>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded
                    @if($review->status === 'approved') bg-emerald-500/20 text-emerald-400
                    @elseif($review->status === 'rejected') bg-red-500/20 text-red-400
                    @else bg-amber-500/20 text-amber-400 @endif">{{ ucfirst($review->status) }}</span>
            </div>
            <p class="text-gray-400 text-sm mt-2 line-clamp-2">{{ \Illuminate\Support\Str::limit($review->content, 200) }}</p>
        </div>
    @empty
        <p class="text-gray-500">You have not submitted any reviews yet.</p>
    @endforelse
    <div class="mt-6">{{ $reviews->links() }}</div>
</div>
@endsection
