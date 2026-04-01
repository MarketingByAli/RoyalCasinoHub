@extends('layouts.app')

@php
    $userReviewVotes = $userReviewVotes ?? collect();
    $relatedCasinos = $relatedCasinos ?? collect();
@endphp

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
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-bold text-white font-serif">{{ $casino->name }}</h1>
                        @if($casino->shows_verified_badge)
                            <span class="inline-flex items-center rounded-full bg-emerald-500/15 border border-emerald-500/40 text-emerald-400 text-xs font-semibold px-2.5 py-0.5" title="Verified listing">Verified</span>
                        @endif
                    </div>
                    @php
                        $locationParts = collect([$casino->locality, $casino->region])->filter()->implode(', ');
                    @endphp
                    <p class="text-gray-400 mt-1">
                        {{ $casino->country }}
                        @if($locationParts)
                            <span class="text-gray-600"> · </span>{{ $locationParts }}
                        @endif
                        @if($casino->established_year)
                            <span class="text-gray-600"> · </span><span class="text-gray-500">Founded {{ $casino->established_year }}</span>
                        @endif
                    </p>
                    @if($casino->average_rating)
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-amber-400 text-xl">
                                <x-star-rating :rating="$casino->average_rating" size="text-xl" />
                            </span>
                            <span class="text-gray-500">({{ $casino->reviews_count }} reviews)</span>
                        </div>
                    @endif
                    @php
                        $dimLabels = ['trust' => 'Trust', 'games' => 'Games', 'support' => 'Support', 'payments' => 'Payments', 'bonuses' => 'Bonuses'];
                        $hasDims = collect($dimLabels)->keys()->contains(fn ($k) => $casino->{'rating_avg_'.$k} !== null);
                    @endphp
                    @if($hasDims)
                        <dl class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-1 text-sm">
                            @foreach($dimLabels as $key => $label)
                                @if($casino->{'rating_avg_'.$key} !== null)
                                    <div class="flex justify-between gap-2 text-gray-400">
                                        <dt>{{ $label }}</dt>
                                        <dd class="text-amber-400 font-medium">{{ number_format((float) $casino->{'rating_avg_'.$key}, 1) }}/5</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    @endif
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        @if($casino->website)
                            <a href="{{ $casino->website }}" target="_blank" rel="nofollow noopener" class="inline-block bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-6 py-2.5 rounded-xl transition-all hover:shadow-lg hover:shadow-amber-500/25">Visit Casino</a>
                        @endif
                        @if(!empty($casino->social_links['linkedin']))
                            <a href="{{ $casino->social_links['linkedin'] }}" target="_blank" rel="nofollow noopener noreferrer" class="inline-flex items-center gap-2 border border-amber-900/40 hover:border-amber-500/50 text-amber-400 font-medium px-4 py-2 rounded-xl transition-colors text-sm">LinkedIn</a>
                        @endif
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 items-center">
                        <button type="button" id="compare-add" class="text-sm border border-amber-900/40 hover:border-amber-500/50 text-amber-400 px-4 py-2 rounded-xl transition-colors">Add to compare</button>
                        @auth
                            <form action="{{ route('favorites.toggle', $casino) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm border border-amber-900/40 hover:border-amber-500/50 text-gray-300 px-4 py-2 rounded-xl transition-colors">
                                    {{ ($isFavorite ?? false) ? 'Saved' : 'Save casino' }}
                                </button>
                            </form>
                            @can('report', $casino)
                                <details class="text-sm">
                                    <summary class="cursor-pointer text-gray-500 hover:text-amber-400/80">Report listing</summary>
                                    <form action="{{ route('casinos.report', $casino) }}" method="POST" class="mt-2 space-y-2 max-w-sm">
                                        @csrf
                                        <select name="reason" required class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-3 py-2 text-white text-sm">
                                            <option value="wrong_info">Wrong information</option>
                                            <option value="spam">Spam</option>
                                            <option value="licensing">Licensing concern</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <textarea name="details" rows="2" maxlength="2000" class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-3 py-2 text-white text-sm" placeholder="Details (optional)"></textarea>
                                        <button type="submit" class="text-xs bg-amber-900/40 text-amber-200 px-3 py-1.5 rounded-lg">Submit</button>
                                    </form>
                                </details>
                            @endcan
                        @endauth
                    </div>
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

        @if($casino->pros || $casino->cons)
            <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-8 mb-8 grid md:grid-cols-2 gap-6">
                @if($casino->pros)
                    <div>
                        <h3 class="text-lg font-semibold text-emerald-400 mb-2">Pros</h3>
                        <div class="prose prose-invert max-w-none text-gray-300 text-sm">{!! nl2br(e($casino->pros)) !!}</div>
                    </div>
                @endif
                @if($casino->cons)
                    <div>
                        <h3 class="text-lg font-semibold text-rose-400 mb-2">Cons</h3>
                        <div class="prose prose-invert max-w-none text-gray-300 text-sm">{!! nl2br(e($casino->cons)) !!}</div>
                    </div>
                @endif
            </div>
        @endif

        @if(is_array($casino->payment_methods) && count($casino->payment_methods))
            <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-8 mb-8">
                <h2 class="text-xl font-semibold text-amber-400 mb-4">Payment methods</h2>
                <ul class="flex flex-wrap gap-2">
                    @foreach($casino->payment_methods as $method)
                        <li class="text-sm px-3 py-1 rounded-lg bg-slate-800/80 border border-amber-900/20 text-gray-300">{{ $method }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($relatedCasinos->isNotEmpty())
            <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-8 mb-8">
                <h2 class="text-xl font-semibold text-amber-400 mb-4">Related casinos</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($relatedCasinos as $rel)
                        <x-casino-card :casino="$rel" />
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-8 mb-8" id="recently-viewed-slot">
            <h2 class="text-xl font-semibold text-amber-400 mb-4">Recently viewed</h2>
            <p class="text-sm text-gray-500 mb-2">Tracked on this device only.</p>
            <ul id="recently-viewed-list" class="text-sm text-gray-400 space-y-1"></ul>
        </div>

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
                        <div>
                            <p class="text-sm text-gray-400 mb-2">Optional: rate specific areas (helps other players)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach(['trust' => 'Trust', 'games' => 'Games', 'support' => 'Support', 'payments' => 'Payments', 'bonuses' => 'Bonuses'] as $key => $label)
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                                        <select name="dimension_ratings[{{ $key }}]" class="w-full bg-slate-900/80 border border-amber-900/20 rounded-xl px-3 py-2 text-white text-sm focus:border-amber-500/40 focus:outline-none">
                                            <option value="">—</option>
                                            @for($i = 5; $i >= 1; $i--)
                                                <option value="{{ $i }}" {{ (int) old('dimension_ratings.'.$key) === $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    @error("dimension_ratings.$key") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                @endforeach
                            </div>
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
                    <div class="flex justify-between items-start gap-4">
                        <h3 class="font-semibold text-white">{{ $review->title }}</h3>
                        <span class="text-amber-400 flex-shrink-0"><x-star-rating :rating="$review->rating" /></span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        by {{ $review->user->name }}
                        @if($review->user->reviewer_credibility_score)
                            <span class="text-gray-600">· reviewer score {{ number_format((float) $review->user->reviewer_credibility_score, 1) }}/5</span>
                        @endif
                        <span class="text-gray-600">· {{ $review->created_at->diffForHumans() }}</span>
                    </p>
                    <p class="text-gray-300 mt-2">{{ $review->content }}</p>
                    <x-review-interactions
                        :review="$review"
                        :casino="$casino"
                        :user-vote="$userReviewVotes[$review->id] ?? null"
                    />
                </div>
            @empty
                <p class="text-gray-500">No reviews yet. Be the first to review!</p>
            @endforelse
        </div>
    </div>

    <div class="lg:w-1/3">
        @if($casino->activeOffers->isNotEmpty())
            <div class="bg-slate-900/60 border border-amber-900/20 rounded-2xl p-6 mb-6">
                <h3 class="font-semibold text-amber-400 mb-4">Offers</h3>
                <ul class="space-y-4">
                    @foreach($casino->activeOffers as $offer)
                        <li class="border-b border-amber-900/10 last:border-0 pb-4 last:pb-0">
                            @if($offer->title)
                                <p class="font-medium text-white">{{ $offer->title }}</p>
                            @endif
                            @if($offer->welcome_bonus_text)
                                <p class="text-sm text-gray-300 mt-1">{{ $offer->welcome_bonus_text }}</p>
                            @endif
                            <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-500">
                                @if($offer->wagering_requirement)
                                    <span>Wagering: {{ $offer->wagering_requirement }}</span>
                                @endif
                                @if($offer->free_spins)
                                    <span>{{ $offer->free_spins }} free spins</span>
                                @endif
                                @if($offer->expires_at)
                                    <span>Ends {{ $offer->expires_at->format('M j, Y') }}</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

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
<script>
(function () {
    const slug = @json($casino->slug);
    const name = @json($casino->name);
    try {
        let raw = localStorage.getItem('rch_recent_v2');
        let items = raw ? JSON.parse(raw) : [];
        if (!Array.isArray(items)) items = [];
        items = items.filter(function (x) { return x && x.slug !== slug; });
        items.unshift({ slug: slug, name: name });
        items = items.slice(0, 12);
        localStorage.setItem('rch_recent_v2', JSON.stringify(items));
        var ul = document.getElementById('recently-viewed-list');
        if (ul) {
            var others = items.filter(function (x) { return x.slug !== slug; }).slice(0, 5);
            if (others.length === 0) {
                ul.textContent = 'Visit more casino pages to build this list.';
            } else {
                others.forEach(function (x) {
                    var li = document.createElement('li');
                    var a = document.createElement('a');
                    a.href = '{{ url('/casino') }}/' + encodeURIComponent(x.slug);
                    a.className = 'text-amber-400 hover:underline';
                    a.textContent = x.name;
                    li.appendChild(a);
                    ul.appendChild(li);
                });
            }
        }
    } catch (e) {}
})();
document.getElementById('compare-add')?.addEventListener('click', function () {
    var s = @json($casino->slug);
    var raw = sessionStorage.getItem('rch_compare');
    var slugs = raw ? JSON.parse(raw) : [];
    if (!Array.isArray(slugs)) slugs = [];
    if (slugs.indexOf(s) === -1) slugs.push(s);
    slugs = slugs.slice(0, 3);
    sessionStorage.setItem('rch_compare', JSON.stringify(slugs));
    window.location.href = '{{ url('/compare') }}?casinos=' + slugs.map(encodeURIComponent).join(',');
});
</script>
@endsection
