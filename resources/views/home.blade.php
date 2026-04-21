@extends('layouts.app')

@section('hero')
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_70%_at_50%_0%,rgba(212,175,55,0.18),transparent)]" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_40%_40%_at_20%_60%,rgba(180,120,30,0.07),transparent)]" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_40%_40%_at_80%_60%,rgba(180,120,30,0.07),transparent)]" aria-hidden="true"></div>

    <div class="absolute top-20 left-[10%] w-72 h-72 bg-amber-500/5 rounded-full blur-3xl animate-pulse-slow pointer-events-none" aria-hidden="true"></div>
    <div class="absolute top-40 right-[8%] w-56 h-56 bg-amber-600/6 rounded-full blur-3xl animate-pulse-slow-delay pointer-events-none" aria-hidden="true"></div>

    <div class="absolute top-8 left-6 opacity-5 text-amber-400 text-8xl font-serif select-none pointer-events-none hidden lg:block" aria-hidden="true">♠</div>
    <div class="absolute top-8 right-6 opacity-5 text-amber-400 text-8xl font-serif select-none pointer-events-none hidden lg:block" aria-hidden="true">♦</div>
    <div class="absolute bottom-16 left-[5%] opacity-4 text-amber-400 text-6xl font-serif select-none pointer-events-none hidden lg:block" aria-hidden="true">♣</div>
    <div class="absolute bottom-16 right-[5%] opacity-4 text-amber-400 text-6xl font-serif select-none pointer-events-none hidden lg:block" aria-hidden="true">♥</div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-28 text-center">

        <div class="inline-flex items-center gap-2.5 bg-amber-500/10 border border-amber-500/25 rounded-full px-6 py-2.5 text-amber-400 text-xs font-semibold uppercase tracking-[0.2em] mb-10 shadow-lg shadow-amber-500/5 backdrop-blur-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
            </svg>
            The World's Premier Casino Directory
        </div>

        <h1 class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold font-serif tracking-tight leading-[1.05] mb-8">
            <span class="text-white">Discover the</span><br>
            <span class="gold-gradient-text">Best Casinos</span>
        </h1>

        <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed mb-12">
            Expert reviews, verified ratings &amp; exclusive bonuses — your trusted guide to
            <span class="text-amber-400 font-semibold">5,000+</span> online casinos worldwide.
        </p>

        <form action="{{ route('search') }}" method="GET" class="max-w-2xl mx-auto mb-6 group">
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

        <div class="flex flex-wrap justify-center gap-2 mb-16 text-xs">
            <span class="text-gray-600">Popular:</span>
            <a href="{{ route('search', ['q' => 'slots']) }}" class="text-gray-500 hover:text-amber-400 transition-colors">Slots</a>
            <span class="text-gray-700">&middot;</span>
            <a href="{{ route('search', ['q' => 'live casino']) }}" class="text-gray-500 hover:text-amber-400 transition-colors">Live Casino</a>
            <span class="text-gray-700">&middot;</span>
            <a href="{{ route('search', ['q' => 'sports betting']) }}" class="text-gray-500 hover:text-amber-400 transition-colors">Sports Betting</a>
            <span class="text-gray-700">&middot;</span>
            <a href="{{ route('search', ['q' => 'poker']) }}" class="text-gray-500 hover:text-amber-400 transition-colors">Poker</a>
            <span class="text-gray-700">&middot;</span>
            <a href="{{ route('search', ['q' => 'no deposit bonus']) }}" class="text-gray-500 hover:text-amber-400 transition-colors">No Deposit Bonus</a>
        </div>

        <div class="inline-flex flex-wrap items-center justify-center gap-6 md:gap-0 divide-y-0 md:divide-x md:divide-amber-900/30 bg-slate-950/60 border border-amber-900/20 rounded-2xl px-4 sm:px-8 py-5 shadow-xl shadow-black/30 backdrop-blur-sm">
            <div class="md:px-8 text-center">
                <div class="text-2xl md:text-3xl font-bold font-serif gold-gradient-text">5,000+</div>
                <div class="text-[11px] text-gray-500 mt-0.5 tracking-wide uppercase">Casinos Listed</div>
            </div>
            <div class="md:px-8 text-center">
                <div class="text-2xl md:text-3xl font-bold font-serif gold-gradient-text">10K+</div>
                <div class="text-[11px] text-gray-500 mt-0.5 tracking-wide uppercase">User Reviews</div>
            </div>
            <div class="md:px-8 text-center">
                <div class="text-2xl md:text-3xl font-bold font-serif gold-gradient-text">100+</div>
                <div class="text-[11px] text-gray-500 mt-0.5 tracking-wide uppercase">Countries</div>
            </div>
            <div class="md:px-8 text-center">
                <div class="text-2xl md:text-3xl font-bold font-serif gold-gradient-text">100%</div>
                <div class="text-[11px] text-gray-500 mt-0.5 tracking-wide uppercase">Independent</div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 inset-x-0 h-32 bg-gradient-to-t from-[#0a0a0f] to-transparent pointer-events-none" aria-hidden="true"></div>
</section>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════
     TOP CASINOS SPOTLIGHT
═══════════════════════════════════════════════ --}}
@if($featuredCasinos->count() >= 3)
<section class="mb-24">
    <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 text-amber-500/80 text-xs font-semibold uppercase tracking-[0.2em] mb-4">
            <span class="w-8 h-px bg-gradient-to-r from-transparent to-amber-500/50"></span>
            Editor's Choice
            <span class="w-8 h-px bg-gradient-to-l from-transparent to-amber-500/50"></span>
        </div>
        <h2 class="text-3xl md:text-4xl font-bold font-serif text-white">Top Rated Casinos</h2>
        <p class="text-gray-500 mt-3 max-w-lg mx-auto text-sm">Hand-picked by our experts based on trust, game variety, bonuses, and player satisfaction.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
        @foreach($featuredCasinos->take(3) as $index => $casino)
        @php
            $rankColors = [
                0 => ['border' => 'border-amber-500/50', 'bg' => 'from-amber-500/15 to-amber-900/10', 'badge' => 'from-amber-400 to-yellow-500', 'badgeText' => 'text-amber-950', 'glow' => 'shadow-amber-500/20', 'label' => 'Gold'],
                1 => ['border' => 'border-slate-400/40', 'bg' => 'from-slate-400/10 to-slate-600/5', 'badge' => 'from-slate-300 to-slate-400', 'badgeText' => 'text-slate-900', 'glow' => 'shadow-slate-400/10', 'label' => 'Silver'],
                2 => ['border' => 'border-amber-700/40', 'bg' => 'from-amber-700/10 to-orange-900/5', 'badge' => 'from-amber-600 to-orange-700', 'badgeText' => 'text-white', 'glow' => 'shadow-amber-700/10', 'label' => 'Bronze'],
            ];
            $rank = $rankColors[$index];
        @endphp
        <a href="{{ route('casino.show', $casino->slug) }}"
           class="group relative flex flex-col bg-slate-900/70 {{ $rank['border'] }} border rounded-3xl overflow-hidden hover:shadow-2xl {{ $rank['glow'] }} transition-all duration-500 {{ $index === 0 ? 'md:scale-105 md:z-10' : '' }}">

            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/40 to-transparent group-hover:via-amber-400/80 transition-all duration-500" aria-hidden="true"></div>

            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl {{ $rank['bg'] }} rounded-full blur-3xl -translate-y-1/2 translate-x-1/4 pointer-events-none opacity-60" aria-hidden="true"></div>

            <div class="relative p-8 flex flex-col flex-1">
                <div class="flex items-start justify-between mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $rank['badge'] }} flex items-center justify-center {{ $rank['badgeText'] }} font-bold text-sm shadow-lg">
                        #{{ $index + 1 }}
                    </div>
                    @if($casino->average_rating)
                        <div class="flex items-center gap-2 bg-slate-950/60 border border-amber-900/20 rounded-full px-3 py-1.5">
                            <span class="text-amber-400 text-sm">★</span>
                            <span class="text-white font-bold text-sm">{{ number_format($casino->average_rating, 1) }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-4 mb-5">
                    @if($casino->logo_url)
                        <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-amber-900/20 p-2 flex items-center justify-center overflow-hidden flex-shrink-0 group-hover:border-amber-500/40 transition-colors">
                            <img src="{{ $casino->logo_url }}" alt="{{ $casino->logo_alt ?? $casino->name }}" width="64" height="64" class="w-full h-full object-contain" loading="lazy">
                        </div>
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500/20 to-amber-900/20 border border-amber-900/30 flex items-center justify-center text-2xl font-bold text-amber-400 font-serif flex-shrink-0">
                            {{ substr($casino->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h3 class="font-bold text-white text-lg group-hover:text-amber-300 transition-colors leading-tight truncate">{{ $casino->name }}</h3>
                        @if($casino->country)
                            <p class="text-xs text-gray-500 mt-1">{{ $casino->country }}</p>
                        @endif
                    </div>
                </div>

                @if($casino->short_description)
                    <p class="text-gray-500 text-xs leading-relaxed mb-5 line-clamp-2">{{ $casino->short_description }}</p>
                @endif

                <div class="flex flex-wrap gap-1.5 mt-auto mb-5">
                    @if($casino->license)
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-400/80 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-lg">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Licensed
                        </span>
                    @endif
                    @if($casino->established_year)
                        <span class="text-[10px] font-medium text-gray-500 bg-slate-800/60 border border-amber-900/15 px-2.5 py-1 rounded-lg">Est. {{ $casino->established_year }}</span>
                    @endif
                    @if($casino->approved_reviews_count ?? 0 > 0)
                        <span class="text-[10px] font-medium text-gray-500 bg-slate-800/60 border border-amber-900/15 px-2.5 py-1 rounded-lg">{{ $casino->approved_reviews_count }} reviews</span>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-amber-900/15">
                    <span class="text-xs font-medium text-amber-500/80 group-hover:text-amber-400 transition-colors">View full review</span>
                    <svg class="w-4 h-4 text-amber-700/60 group-hover:text-amber-400 group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     ALL FEATURED CASINOS GRID
═══════════════════════════════════════════════ --}}
<section class="mb-24">
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
        @forelse($featuredCasinos->count() > 3 ? $featuredCasinos->skip(3) : $featuredCasinos as $casino)
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
     HOW IT WORKS
═══════════════════════════════════════════════ --}}
<section class="mb-24">
    <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 text-amber-500/80 text-xs font-semibold uppercase tracking-[0.2em] mb-4">
            <span class="w-8 h-px bg-gradient-to-r from-transparent to-amber-500/50"></span>
            Simple Process
            <span class="w-8 h-px bg-gradient-to-l from-transparent to-amber-500/50"></span>
        </div>
        <h2 class="text-3xl md:text-4xl font-bold font-serif text-white">How It Works</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
        <div class="hidden md:block absolute top-16 left-[20%] right-[20%] h-px bg-gradient-to-r from-amber-500/0 via-amber-500/30 to-amber-500/0" aria-hidden="true"></div>

        @php
        $steps = [
            ['num' => '01', 'title' => 'Search & Filter', 'desc' => 'Browse by country, game type, bonus offers or use our smart search to find casinos that match your preferences.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'],
            ['num' => '02', 'title' => 'Compare & Read', 'desc' => 'Read expert reviews, compare ratings across trust, games, support, payments, and check real player experiences.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
            ['num' => '03', 'title' => 'Play with Confidence', 'desc' => 'Choose a verified casino knowing you have all the information you need for a safe, enjoyable experience.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
        ];
        @endphp

        @foreach($steps as $step)
        <div class="relative text-center group">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mx-auto mb-6 group-hover:bg-amber-500/15 group-hover:border-amber-500/40 transition-all duration-300 relative z-10">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    {!! $step['icon'] !!}
                </svg>
            </div>
            <div class="text-[10px] font-bold text-amber-500/50 uppercase tracking-widest mb-2">Step {{ $step['num'] }}</div>
            <h3 class="font-bold text-white text-lg mb-2">{{ $step['title'] }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed max-w-xs mx-auto">{{ $step['desc'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     BROWSE BY COUNTRY
═══════════════════════════════════════════════ --}}
@if($countries->isNotEmpty())
<section class="mb-24">
    <div class="section-header mb-8">
        <div class="section-title-bar"></div>
        <h2 class="section-title">Browse by Country</h2>
        <span class="text-gray-600 text-xs ml-2">{{ $countries->count() }} countries</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @foreach($countries->take(18) as $c)
            <a href="{{ route('country.show', $c->country_slug) }}"
               class="group relative flex items-center gap-3 bg-slate-900/60 hover:bg-amber-500/10 border border-amber-900/20 hover:border-amber-500/40 px-4 py-3.5 rounded-xl transition-all duration-300 overflow-hidden">
                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/0 to-transparent group-hover:via-amber-500/40 transition-all duration-300" aria-hidden="true"></div>
                <div class="w-8 h-8 rounded-lg bg-slate-800/80 border border-amber-900/15 flex items-center justify-center flex-shrink-0 group-hover:border-amber-500/30 transition-colors">
                    <svg class="w-4 h-4 text-amber-600/60 group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-400 group-hover:text-amber-300 transition-colors truncate">{{ $c->country }}</span>
            </a>
        @endforeach
    </div>

    @if($countries->count() > 18)
        <div class="text-center mt-6">
            <a href="{{ route('search') }}" class="inline-flex items-center gap-2 text-amber-400/70 hover:text-amber-300 text-sm font-medium transition-colors">
                View all {{ $countries->count() }} countries
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    @endif
</section>
@endif

{{-- ═══════════════════════════════════════════════
     WHY TRUST US
═══════════════════════════════════════════════ --}}
<section class="mb-24">
    <div class="relative rounded-3xl border border-amber-900/20 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900/90 via-[#0f0f18] to-amber-950/20" aria-hidden="true"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/6 rounded-full blur-3xl -translate-y-1/3 translate-x-1/3 pointer-events-none" aria-hidden="true"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-amber-600/4 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3 pointer-events-none" aria-hidden="true"></div>

        <div class="relative p-8 md:p-12 lg:p-16">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 text-amber-500/80 text-xs font-semibold uppercase tracking-[0.2em] mb-4">
                    <span class="w-8 h-px bg-gradient-to-r from-transparent to-amber-500/50"></span>
                    Our Promise
                    <span class="w-8 h-px bg-gradient-to-l from-transparent to-amber-500/50"></span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold font-serif text-white">Why Trust RoyalCasinoHub</h2>
                <p class="text-gray-500 mt-3 max-w-lg mx-auto text-sm">Every casino on our platform goes through a rigorous review process.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                $trustFeatures = [
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                        'title' => 'Verified Legitimacy',
                        'desc' => 'Every casino is checked for valid licenses and regulatory compliance before listing.',
                        'stat' => '100%',
                        'statLabel' => 'Verified',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
                        'title' => 'Honest Ratings',
                        'desc' => 'Real player reviews and expert analysis — no paid placements influence our scores.',
                        'stat' => '10K+',
                        'statLabel' => 'Reviews',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        'title' => 'Best Bonuses',
                        'desc' => 'We track the latest exclusive offers and welcome bonuses updated in real time.',
                        'stat' => 'Daily',
                        'statLabel' => 'Updated',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        'title' => 'Global Coverage',
                        'desc' => 'Browse casinos in 100+ countries with localized regulations and availability.',
                        'stat' => '100+',
                        'statLabel' => 'Countries',
                    ],
                ];
                @endphp
                @foreach($trustFeatures as $feat)
                    <div class="group relative bg-slate-900/40 border border-amber-900/15 rounded-2xl p-6 hover:border-amber-500/30 hover:bg-slate-900/60 transition-all duration-300">
                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/0 to-transparent group-hover:via-amber-500/30 transition-all duration-300 rounded-t-2xl" aria-hidden="true"></div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-500/15 transition-colors">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    {!! $feat['icon'] !!}
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold font-serif gold-gradient-text">{{ $feat['stat'] }}</div>
                                <div class="text-[10px] text-gray-600 uppercase tracking-wide">{{ $feat['statLabel'] }}</div>
                            </div>
                        </div>
                        <h3 class="font-semibold text-white text-sm mb-2">{{ $feat['title'] }}</h3>
                        <p class="text-gray-500 text-xs leading-relaxed">{{ $feat['desc'] }}</p>
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
<section class="mb-24">
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($latestReviews as $review)
            <div class="group relative bg-slate-900/60 border border-amber-900/20 rounded-2xl overflow-hidden hover:border-amber-500/40 hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-300">
                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/20 to-transparent group-hover:via-amber-500/50 transition-all duration-300" aria-hidden="true"></div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500/20 to-amber-900/30 border border-amber-900/30 flex items-center justify-center text-sm font-bold text-amber-400 flex-shrink-0">
                            {{ $review->user ? substr($review->user->name, 0, 1) : '?' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-white truncate">{{ $review->user?->name ?? 'Anonymous' }}</div>
                            <div class="text-[11px] text-gray-600">{{ $review->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="flex-shrink-0 text-amber-400/90">
                            <x-star-rating :rating="$review->rating" />
                        </div>
                    </div>

                    <a href="{{ route('casino.show', $review->casino->slug) }}"
                       class="inline-flex items-center gap-1.5 text-[11px] font-bold text-amber-500/80 hover:text-amber-300 uppercase tracking-widest transition-colors mb-3">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $review->casino->name }}
                    </a>

                    @if($review->title)
                        <p class="text-white font-semibold text-sm mb-2 leading-snug">{{ $review->title }}</p>
                    @endif
                    <p class="text-gray-500 text-xs leading-relaxed">{{ Str::limit($review->content, 140) }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     CTA BANNER
═══════════════════════════════════════════════ --}}
@guest
<section class="mb-10">
    <div class="relative rounded-3xl border border-amber-900/20 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-amber-950/40 via-[#0f0f18] to-amber-950/40" aria-hidden="true"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[500px] h-[500px] bg-amber-500/8 rounded-full blur-3xl -translate-y-2/3 pointer-events-none" aria-hidden="true"></div>

        <div class="relative px-8 py-16 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 mb-6">
                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold font-serif text-white mb-3">Join Our Community</h2>
            <p class="text-gray-400 text-sm max-w-md mx-auto mb-8">Create an account to write reviews, save favorites, and get personalized casino recommendations.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-yellow-300 text-amber-950 font-bold px-8 py-3.5 rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-amber-500/30 text-sm tracking-wide">
                    Create Free Account
                </a>
                <a href="{{ route('login') }}" class="text-gray-400 hover:text-amber-400 text-sm font-medium transition-colors">
                    Already have an account? Sign in
                </a>
            </div>
        </div>
    </div>
</section>
@endguest

@endsection
