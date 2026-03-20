@extends('layouts.app')

@section('content')
<nav class="mb-6 text-sm" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Home</a></li>
        <li class="text-gray-600">/</li>
        <li class="text-amber-400">Reviews</li>
    </ol>
</nav>

<h1 class="text-3xl md:text-4xl font-bold text-white font-serif mb-2">Latest Casino Reviews</h1>
<p class="text-gray-500 mb-10">Honest reviews from real players.</p>

<div class="space-y-4">
    @foreach($reviews as $review)
        <div class="bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 hover:border-amber-900/30 transition-colors">
            <div class="flex justify-between items-start gap-4">
                <div class="min-w-0">
                    <a href="{{ route('casino.show', $review->casino->slug) }}" class="text-lg font-semibold text-amber-400 hover:text-amber-300 transition-colors">{{ $review->casino->name }}</a>
                    <h2 class="text-white font-medium mt-1">{{ $review->title }}</h2>
                    <p class="text-gray-400 mt-2">{{ Str::limit($review->content, 300) }}</p>
                    <p class="text-sm text-gray-500 mt-2">by {{ $review->user->name }} · {{ $review->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-amber-400/90 flex-shrink-0"><x-star-rating :rating="$review->rating" /></span>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-10">{{ $reviews->links() }}</div>
@endsection
