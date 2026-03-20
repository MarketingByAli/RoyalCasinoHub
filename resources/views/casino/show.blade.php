@extends('layouts.app')

@section('content')
<nav class="mb-6 text-sm" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Home</a></li>
        <li class="text-gray-600">/</li>
        <li><a href="{{ route('country.show', $casino->country_slug) }}" class="hover:text-amber-400 transition-colors">{{ $casino->country }}</a></li>
        <li class="text-gray-600">/</li>
        <li class="text-amber-400">{{ $casino->name }}</li>
    </ol>
</nav>

<div class="flex flex-col lg:flex-row gap-8">
    <div class="lg:w-2/3">
        <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-8 mb-8">
            <div class="flex flex-col sm:flex-row gap-6 items-start">
                @if($casino->logo_url)
                    <img src="{{ $casino->logo_url }}" alt="{{ $casino->logo_alt ?? $casino->name }} logo" width="120" height="120" class="w-28 h-28 object-contain rounded-xl flex-shrink-0" loading="eager">
                @endif
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white font-serif">{{ $casino->name }}</h1>
                    <p class="text-gray-400 mt-1">{{ $casino->country }}</p>
                    @if($casino->average_rating)
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-amber-400 text-xl">
                                <x-star-rating :rating="$casino->average_rating" size="text-xl" />
                            </span>
                            <span class="text-gray-500">({{ $casino->reviews_count }} reviews)</span>
                        </div>
                    @endif
                    @if($casino->website)
                        <a href="{{ $casino->website }}" target="_blank" rel="nofollow noopener" class="inline-block mt-4 bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-6 py-2.5 rounded-xl transition-all hover:shadow-lg hover:shadow-amber-500/25">Visit Casino</a>
                    @endif
                </div>
            </div>
        </div>

        @if($casino->screenshot_url)
            <div class="mb-8">
                <img src="{{ $casino->screenshot_url }}" alt="{{ $casino->screenshot_alt ?? $casino->name }} screenshot" width="1200" height="630" class="w-full rounded-2xl border border-amber-900/20" loading="lazy">
            </div>
        @endif

        @if($casino->description)
            <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-8 mb-8">
                <h2 class="text-xl font-semibold text-amber-400 mb-4">About {{ $casino->name }}</h2>
                <div class="prose prose-invert max-w-none text-gray-300">{!! nl2br(e($casino->description)) !!}</div>
            </div>
        @endif

        <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-8 mb-8">
            <h2 class="text-xl font-semibold text-amber-400 mb-4">Write a Review</h2>
            @auth
                <form action="{{ route('reviews.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="casino_id" value="{{ $casino->id }}">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Rating</label>
                            <select name="rating" required class="w-full bg-slate-900/80 border border-amber-900/20 rounded-xl px-4 py-2 text-white focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 focus:outline-none @error('rating') border-red-500 @enderror">
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }} stars</option>
                                @endfor
                            </select>
                            @error('rating') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" required maxlength="255" class="w-full bg-slate-900/80 border border-amber-900/20 rounded-xl px-4 py-2 text-white focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 focus:outline-none @error('title') border-red-500 @enderror" placeholder="Summary of your experience">
                            @error('title') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Your Review</label>
                            <textarea name="content" required rows="4" maxlength="5000" class="w-full bg-slate-900/80 border border-amber-900/20 rounded-xl px-4 py-2 text-white focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 focus:outline-none @error('content') border-red-500 @enderror" placeholder="Share your experience...">{{ old('content') }}</textarea>
                            @error('content') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-6 py-2.5 rounded-xl transition-all hover:shadow-lg hover:shadow-amber-500/25">Submit Review</button>
                    </div>
                </form>
            @else
                <p class="text-gray-400"><a href="{{ route('login') }}" class="text-amber-400 hover:underline">Log in</a> to write a review.</p>
            @endauth
        </div>

        <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-8">
            <h2 class="text-xl font-semibold text-amber-400 mb-4">User Reviews</h2>
            @forelse($casino->approvedReviews as $review)
                <div class="border-b border-amber-900/10 pb-4 mb-4 last:border-0 last:mb-0 last:pb-0">
                    <div class="flex justify-between items-start">
                        <h3 class="font-semibold text-white">{{ $review->title }}</h3>
                        <span class="text-amber-400"><x-star-rating :rating="$review->rating" /></span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">by {{ $review->user->name }}</p>
                    <p class="text-gray-300 mt-2">{{ $review->content }}</p>
                </div>
            @empty
                <p class="text-gray-500">No reviews yet. Be the first to review!</p>
            @endforelse
        </div>
    </div>

    <div class="lg:w-1/3">
        @if(!$casino->is_claimed)
            <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-6 mb-6 sticky top-24">
                <h3 class="font-semibold text-amber-400 mb-2">Is this your casino?</h3>
                <p class="text-sm text-gray-400 mb-4">Claim this listing to manage your information.</p>
                @auth
                    <form action="{{ route('claim.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="casino_id" value="{{ $casino->id }}">
                        <button type="submit" class="w-full bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/40 text-amber-400 font-semibold px-4 py-2.5 rounded-xl transition-all hover:border-amber-500/60">Claim This Listing</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/40 text-amber-400 font-semibold px-4 py-2.5 rounded-xl transition-all hover:border-amber-500/60">Claim This Listing</a>
                @endauth
            </div>
        @endif

        @if($casino->news->isNotEmpty())
            <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-6">
                <h3 class="font-semibold text-amber-400 mb-4">Latest News</h3>
                <ul class="space-y-3">
                    @foreach($casino->news as $news)
                        <li>
                            <a href="{{ $news->url }}" target="_blank" rel="nofollow noopener" class="text-gray-300 hover:text-amber-400 transition">{{ $news->title }}</a>
                            <p class="text-xs text-gray-500 mt-1">{{ $news->published_at?->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection
