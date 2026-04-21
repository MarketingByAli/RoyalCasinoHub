@extends('layouts.app')

@section('hero')
{{-- ═══════════════════════════════════════════════
     FULL-BLEED HERO
═══════════════════════════════════════════════ --}}
<section class="relative overflow-hidden">
    {{-- Layered background atmosphere --}}
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_70%_at_50%_0%,rgba(212,175,55,0.18),transparent)]" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_40%_40%_at_20%_60%,rgba(180,120,30,0.07),transparent)]" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_40%_40%_at_80%_60%,rgba(180,120,30,0.07),transparent)]" aria-hidden="true"></div>

    {{-- Decorative floating orbs --}}
    <div class="absolute top-20 left-[10%] w-72 h-72 bg-amber-500/5 rounded-full blur-3xl animate-pulse-slow pointer-events-none" aria-hidden="true"></div>
    <div class="absolute top-40 right-[8%] w-56 h-56 bg-amber-600/6 rounded-full blur-3xl animate-pulse-slow-delay pointer-events-none" aria-hidden="true"></div>

    {{-- Decorative card suits (top corners) --}}
    <div class="absolute top-8 left-6 opacity-5 text-amber-400 text-8xl font-serif select-none pointer-events-none hidden lg:block" aria-hidden="true">♠</div>
    <div class="absolute top-8 right-6 opacity-5 text-amber-400 text-8xl font-serif select-none pointer-events-none hidden lg:block" aria-hidden="true">♦</div>
    <div class="absolute bottom-10 left-[5%] opacity-4 text-amber-400 text-6xl font-serif select-none pointer-events-none hidden lg:block" aria-hidden="true">♣</div>
    <div class="absolute bottom-10 right-[5%] opacity-4 text-amber-400 text-6xl font-serif select-none pointer-events-none hidden lg:block" aria-hidden="true">♥</div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 text-center">

        {{-- Crown badge --}}
        <div class="inline-flex items-center gap-2 bg-amber-500/10 border border-amber-500/25 rounded-full px-5 py-2 text-amber-400 text-xs font-semibold uppercase tracking-widest mb-10 shadow-lg shadow-amber-500/5">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M2.5 19h19v2h-19v-2zm9.57-14.82L12 3.26l-.57 0.92-8.73 5.04L4.5 17h15l1.8-7.78-8.73-5.04zM12 5.5l7 4.04L17.5 15h-11L5 9.54 12 5.5z"/>
            </svg>
            The World's #1 Casino Review Platform
        </div>

        {{-- Main headline --}}
        <h1 class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold font-serif tracking-tight leading-[1.05] mb-6">
            <span class="text-white">Find Your</span><br>
            <span class="gold-gradient-text">Perfect Casino</span>
        </h1>

        <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed mb-12">
            Trusted expert reviews, honest ratings &amp; exclusive bonuses across
            <span class="text-amber-400 font-semibold">5,000+</span> online casinos worldwide.
        </p>

        {{-- Search bar --}}
        <form action="{{ route('search') }}" method="GET" class="max-w-2xl mx-auto mb-14 group">
            <div class="relative flex items-center shadow-2xl shadow-black/40">
                <div class="absolute left-5 text-gray-500 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search" name="q" placeholder="Search casino name, country or feature..."
                    class="w-full bg-slate-950/90 border border-amber-900/30 rounded-2xl pl-14 pr-40 py-5 text-white placeholder-gray-600 focus:border-amber-500/60 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition-all text-base">
                <button type="submit"
                    class="absolute right-2.5 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-yellow-300 text-amber-950 font-bold px-7 py-3 rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-amber-500/40 text-sm tracking-wide">
                    Search
                </button>
            </div>
        </form>

        {{-- Trust stats row --}}
        <div class="inline-flex flex-wrap items-center justify-center gap-6 md:gap-0 divide-y-0 md:divide-x md:divide-amber-900/30 bg-slate-950/60 border border-amber-900/20 rounded-2xl px-8 py-5 shadow-xl shadow-black/30">
            <div class="md:px-8 text-center">
                <div class="text-2xl md:text-3xl font-bold font-serif gold-gradient-text">5,000+</div>
                <div class="text-xs text-gray-500 mt-0.5 tracking-wide">Casinos Listed</div>
            </div>
            <div class="md:px-8 text-center">
                <div class="text-2xl md:text-3xl font-bold font-serif gold-gradient-text">10K+</div>
                <div class="text-xs text-gray-500 mt-0.5 tracking-wide">User Reviews</div>
            </div>
            <div class="md:px-8 text-center">
                <div class="text-2xl md:text-3xl font-bold font-serif gold-gradient-text">100+</div>
                <div class="text-xs text-gray-500 mt-0.5 tracking-wide">Countries</div>
            </div>
            <div class="md:px-8 text-center">
                <div class="text-2xl md:text-3xl font-bold font-serif gold-gradient-text">100%</div>
                <div class="text-xs text-gray-500 mt-0.5 tracking-wide">Independent</div>
            </div>
        </div>
    </div>

    {{-- Bottom fade to page background --}}
    <div class="absolute bottom-0 inset-x-0 h-32 bg-gradient-to-t from-[#0a0a0f] to-transparent pointer-events-none" aria-hidden="true"></div>
</section>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════
     BROWSE BY COUNTRY
═══════════════════════════════════════════════ --}}
@if($countries->isNotEmpty())
<section class="mb-20">
    <div class="section-header mb-6">
        <div class="section-title-bar"></div>
        <h2 class="section-title">Browse by Country</h2>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach($countries as $c)
            <a href="{{ route('country.show', $c->country_slug) }}"
               class="group flex items-center gap-2 bg-slate-900/60 hover:bg-amber-500/10 border border-amber-900/20 hover:border-amber-500/50 px-4 py-2 rounded-xl text-gray-400 hover:text-amber-300 transition-all duration-200 text-sm font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-900/50 group-hover:bg-amber-400 transition-colors flex-shrink-0"></span>
                {{ $c->country }}
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     FEATURED CASINOS
═══════════════════════════════════════════════ --}}
<section class="mb-20">
    <div class="section-header mb-8">
        <div class="section-title-bar"></div>
        <h2 class="section-title">Featured Casinos</h2>
        <span class="hidden sm:inline-flex items-center text-xs text-amber-500/80 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded-full font-semibold ml-2">
            ★ Top Rated
        </span>
        <a href="{{ route('search') }}" class="ml-auto text-amber-400/70 hover:text-amber-300 text-sm font-medium flex items-center gap-1 transition-colors">
            View all
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($featuredCasinos as $casino)
            <x-casino-card :casino="$casino" />
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 bg-slate-900/80 rounded-2xl flex items-center justify-center mb-4 border border-amber-900/20">
                    <svg class="w-7 h-7 text-amber-900/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-gray-600 text-sm">No casinos yet. Import some via the admin panel.</p>
            </div>
        @endforelse
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     WHY TRUST US
═══════════════════════════════════════════════ --}}
<section class="mb-20">
    <div class="relative rounded-3xl border border-amber-900/20 overflow-hidden">
        {{-- Background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900/90 via-[#0f0f18] to-amber-950/20" aria-hidden="true"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/6 rounded-full blur-3xl -translate-y-1/3 translate-x-1/3 pointer-events-none" aria-hidden="true"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-amber-600/4 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3 pointer-events-none" aria-hidden="true"></div>

        <div class="relative p-8 md:p-12">
            <div class="section-header mb-10">
                <div class="section-title-bar"></div>
                <h2 class="section-title">Why Trust RoyalCasinoHub</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @php
                $trustFeatures = [
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                        'title' => 'Verified Legitimacy',
                        'desc' => 'Every casino is checked for valid licenses and regulatory compliance before listing.',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
                        'title' => 'Honest Ratings',
                        'desc' => 'Real player reviews and expert analysis — no paid placements influence our scores.',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        'title' => 'Best Bonuses',
                        'desc' => 'We track the latest exclusive offers and welcome bonuses updated in real time.',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        'title' => 'Global Coverage',
                        'desc' => 'Browse casinos in 100+ countries with localized regulations and availability.',
                    ],
                ];
                @endphp
                @foreach($trustFeatures as $feat)
                    <div class="flex flex-col gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                {!! $feat['icon'] !!}
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white text-sm mb-1.5">{{ $feat['title'] }}</h3>
                            <p class="text-gray-500 text-xs leading-relaxed">{{ $feat['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     LATEST PLAYER REVIEWS
═══════════════════════════════════════════════ --}}
@if($latestReviews->isNotEmpty())
<section class="mb-20">
    <div class="section-header mb-8">
        <div class="section-title-bar"></div>
        <h2 class="section-title">Latest Player Reviews</h2>
        <a href="{{ route('reviews.index') }}" class="ml-auto text-amber-400/70 hover:text-amber-300 text-sm font-medium flex items-center gap-1 transition-colors">
            All reviews
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($latestReviews as $review)
            <div class="group relative bg-slate-900/60 border border-amber-900/20 rounded-2xl p-6 hover:border-amber-500/40 hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/20 to-transparent group-hover:via-amber-500/50 transition-all duration-300" aria-hidden="true"></div>
                <div class="flex items-start justify-between gap-4 mb-3">
                    <a href="{{ route('casino.show', $review->casino->slug) }}"
                       class="text-xs font-bold text-amber-500/90 hover:text-amber-300 uppercase tracking-widest transition-colors">
                        {{ $review->casino->name }}
                    </a>
                    <div class="flex-shrink-0 text-amber-400/90">
                        <x-star-rating :rating="$review->rating" />
                    </div>
                </div>
                <p class="text-white font-semibold text-sm mb-2 leading-snug">{{ $review->title }}</p>
                <p class="text-gray-500 text-xs leading-relaxed">{{ Str::limit($review->content, 160) }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif

@endsection
