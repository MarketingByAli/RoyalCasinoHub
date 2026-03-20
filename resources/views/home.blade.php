@extends('layouts.app')

@section('content')
<section class="mb-16">
    <h1 class="text-4xl md:text-6xl font-bold text-white font-serif tracking-tight mb-4">
        Find Your <span class="text-amber-400">Perfect</span> Casino
    </h1>
    <p class="text-lg md:text-xl text-gray-400 max-w-2xl leading-relaxed">Trusted reviews, honest ratings, and the best bonuses. Browse 5,000+ online casinos.</p>
</section>

<form action="{{ route('search') }}" method="GET" class="mb-16">
    <div class="flex gap-3 max-w-2xl">
        <input type="search" name="q" placeholder="Search by casino name..."
            class="flex-1 bg-slate-900/80 border border-amber-900/20 rounded-xl px-5 py-3.5 text-white placeholder-gray-500 focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 focus:outline-none transition-all">
        <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-8 py-3.5 rounded-xl transition-all hover:shadow-lg hover:shadow-amber-500/25">
            Search
        </button>
    </div>
</form>

@if($countries->isNotEmpty())
<section class="mb-16">
    <h2 class="text-sm font-semibold text-amber-400/90 uppercase tracking-wider mb-4">Browse by Country</h2>
    <div class="flex flex-wrap gap-2">
        @foreach($countries as $c)
            <a href="{{ route('country.show', $c->country_slug) }}" class="group bg-slate-900/60 hover:bg-amber-500/10 border border-amber-900/20 hover:border-amber-500/40 px-4 py-2.5 rounded-lg text-gray-300 hover:text-amber-400 transition-all duration-200">
                {{ $c->country }}
            </a>
        @endforeach
    </div>
</section>
@endif

<section class="mb-16">
    <h2 class="text-sm font-semibold text-amber-400/90 uppercase tracking-wider mb-6">Featured Casinos</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($featuredCasinos as $casino)
            <x-casino-card :casino="$casino" />
        @empty
            <p class="col-span-full text-gray-500 py-8">No casinos yet. Import some via the admin panel.</p>
        @endforelse
    </div>
</section>

@if($latestReviews->isNotEmpty())
<section>
    <h2 class="text-sm font-semibold text-amber-400/90 uppercase tracking-wider mb-6">Latest Reviews</h2>
    <div class="space-y-4">
        @foreach($latestReviews as $review)
            <div class="bg-slate-900/60 border border-amber-900/20 rounded-xl p-5 hover:border-amber-900/30 transition-colors">
                <div class="flex justify-between items-start gap-4">
                    <div class="min-w-0">
                        <a href="{{ route('casino.show', $review->casino->slug) }}" class="font-semibold text-amber-400 hover:text-amber-300 transition-colors">{{ $review->casino->name }}</a>
                        <p class="text-white font-medium mt-1">{{ $review->title }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ Str::limit($review->content, 150) }}</p>
                    </div>
                    <div class="text-amber-400/90 flex-shrink-0">
                        <x-star-rating :rating="$review->rating" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
@endsection
