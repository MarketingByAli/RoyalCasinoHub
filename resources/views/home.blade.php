@extends('layouts.app')

@section('hero')
<section class="relative overflow-hidden" style="min-height:520px">
    <div class="absolute inset-0" style="background:radial-gradient(ellipse 80% 70% at 50% 0%,rgba(212,175,55,0.18),transparent)" aria-hidden="true"></div>
    <div class="absolute inset-0" style="background:radial-gradient(ellipse 40% 40% at 20% 60%,rgba(180,120,30,0.07),transparent)" aria-hidden="true"></div>
    <div class="absolute inset-0" style="background:radial-gradient(ellipse 40% 40% at 80% 60%,rgba(180,120,30,0.07),transparent)" aria-hidden="true"></div>

    <div class="absolute top-20 left-[10%] w-72 h-72 rounded-full blur-3xl animate-pulse-slow pointer-events-none" style="background:rgba(245,158,11,0.06)" aria-hidden="true"></div>
    <div class="absolute top-40 right-[8%] w-56 h-56 rounded-full blur-3xl animate-pulse-slow-delay pointer-events-none" style="background:rgba(217,119,6,0.05)" aria-hidden="true"></div>

    <div class="absolute top-8 left-6 hidden lg:block select-none pointer-events-none" style="opacity:0.04;color:#d4af37;font-size:6rem;font-family:serif" aria-hidden="true">♠</div>
    <div class="absolute top-8 right-6 hidden lg:block select-none pointer-events-none" style="opacity:0.04;color:#d4af37;font-size:6rem;font-family:serif" aria-hidden="true">♦</div>
    <div class="absolute bottom-20 left-[5%] hidden lg:block select-none pointer-events-none" style="opacity:0.03;color:#d4af37;font-size:4.5rem;font-family:serif" aria-hidden="true">♣</div>
    <div class="absolute bottom-20 right-[5%] hidden lg:block select-none pointer-events-none" style="opacity:0.03;color:#d4af37;font-size:4.5rem;font-family:serif" aria-hidden="true">♥</div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 sm:pt-24 pb-28 text-center">

        <div class="inline-flex items-center gap-2.5 rounded-full px-6 py-2.5 mb-10" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);box-shadow:0 4px 24px rgba(245,158,11,0.05)">
            <svg class="w-4 h-4" style="color:#d4af37" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
            </svg>
            <span style="color:#d4af37;font-size:11px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase">The World's Premier Casino Directory</span>
        </div>

        <h1 class="mb-8" style="font-family:'Playfair Display',Georgia,serif;font-weight:700;line-height:1.05;letter-spacing:-0.02em">
            <span class="block text-white" style="font-size:clamp(2.5rem,6vw,5rem)">Discover the</span>
            <span class="block gold-gradient-text" style="font-size:clamp(3rem,8vw,6.5rem)">Best Casinos</span>
        </h1>

        <p class="max-w-2xl mx-auto mb-12" style="color:#9ca3af;font-size:clamp(1rem,2vw,1.25rem);line-height:1.7">
            Expert reviews, verified ratings &amp; exclusive bonuses — your trusted guide to
            <span style="color:#fbbf24;font-weight:600">5,000+</span> online casinos worldwide.
        </p>

        <form action="{{ route('search') }}" method="GET" class="max-w-2xl mx-auto mb-6">
            <div class="relative flex items-center" style="box-shadow:0 25px 60px rgba(0,0,0,0.5),0 0 40px rgba(212,175,55,0.08);border-radius:16px">
                <div style="position:absolute;left:20px;top:50%;transform:translateY(-50%);color:#6b7280;pointer-events:none;display:flex;align-items:center">
                    <svg style="width:20px;height:20px" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search" name="q" placeholder="Search casino name, country or feature..."
                    style="width:100%;background:rgba(15,15,25,0.95);border:1px solid rgba(180,120,30,0.3);border-radius:16px;padding:20px 160px 20px 56px;color:#fff;font-size:16px;outline:none;transition:border-color 0.2s"
                    onfocus="this.style.borderColor='rgba(245,158,11,0.5)'" onblur="this.style.borderColor='rgba(180,120,30,0.3)'">
                <button type="submit"
                    style="position:absolute;right:10px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#451a03;font-weight:700;padding:12px 28px;border-radius:12px;border:none;font-size:14px;letter-spacing:0.02em;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 16px rgba(245,158,11,0.3)"
                    onmouseover="this.style.background='linear-gradient(135deg,#fbbf24,#f59e0b)';this.style.boxShadow='0 6px 24px rgba(245,158,11,0.4)'"
                    onmouseout="this.style.background='linear-gradient(135deg,#f59e0b,#d97706)';this.style.boxShadow='0 4px 16px rgba(245,158,11,0.3)'">
                    Search
                </button>
            </div>
        </form>

        <div class="flex flex-wrap justify-center gap-2 mb-14" style="font-size:12px">
            <span style="color:#6b7280">Popular:</span>
            <a href="{{ route('search', ['q' => 'slots']) }}" style="color:#9ca3af;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='#9ca3af'">Slots</a>
            <span style="color:#4b5563">&middot;</span>
            <a href="{{ route('search', ['q' => 'live casino']) }}" style="color:#9ca3af;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='#9ca3af'">Live Casino</a>
            <span style="color:#4b5563">&middot;</span>
            <a href="{{ route('search', ['q' => 'sports betting']) }}" style="color:#9ca3af;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='#9ca3af'">Sports Betting</a>
            <span style="color:#4b5563">&middot;</span>
            <a href="{{ route('search', ['q' => 'poker']) }}" style="color:#9ca3af;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='#9ca3af'">Poker</a>
            <span style="color:#4b5563">&middot;</span>
            <a href="{{ route('search', ['q' => 'no deposit bonus']) }}" style="color:#9ca3af;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='#9ca3af'">No Deposit Bonus</a>
        </div>

        <div style="display:inline-flex;flex-wrap:wrap;align-items:center;justify-content:center;background:rgba(10,10,20,0.7);border:1px solid rgba(180,120,30,0.2);border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,0.3);backdrop-filter:blur(12px)">
            <div style="padding:20px 28px;text-align:center">
                <div class="gold-gradient-text" style="font-size:1.75rem;font-weight:700;font-family:'Playfair Display',serif">5,000+</div>
                <div style="font-size:10px;color:#6b7280;margin-top:2px;letter-spacing:0.1em;text-transform:uppercase">Casinos Listed</div>
            </div>
            <div style="width:1px;height:40px;background:rgba(180,120,30,0.2);flex-shrink:0" aria-hidden="true"></div>
            <div style="padding:20px 28px;text-align:center">
                <div class="gold-gradient-text" style="font-size:1.75rem;font-weight:700;font-family:'Playfair Display',serif">10K+</div>
                <div style="font-size:10px;color:#6b7280;margin-top:2px;letter-spacing:0.1em;text-transform:uppercase">User Reviews</div>
            </div>
            <div style="width:1px;height:40px;background:rgba(180,120,30,0.2);flex-shrink:0" aria-hidden="true"></div>
            <div style="padding:20px 28px;text-align:center">
                <div class="gold-gradient-text" style="font-size:1.75rem;font-weight:700;font-family:'Playfair Display',serif">100+</div>
                <div style="font-size:10px;color:#6b7280;margin-top:2px;letter-spacing:0.1em;text-transform:uppercase">Countries</div>
            </div>
            <div style="width:1px;height:40px;background:rgba(180,120,30,0.2);flex-shrink:0" aria-hidden="true"></div>
            <div style="padding:20px 28px;text-align:center">
                <div class="gold-gradient-text" style="font-size:1.75rem;font-weight:700;font-family:'Playfair Display',serif">100%</div>
                <div style="font-size:10px;color:#6b7280;margin-top:2px;letter-spacing:0.1em;text-transform:uppercase">Independent</div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 inset-x-0 h-32 pointer-events-none" style="background:linear-gradient(to top,#0a0a0f,transparent)" aria-hidden="true"></div>
</section>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════
     TOP CASINOS SPOTLIGHT
═══════════════════════════════════════════════ --}}
@if($featuredCasinos->count() >= 3)
<section style="margin-bottom:96px">
    <div class="text-center" style="margin-bottom:48px">
        <div class="inline-flex items-center gap-2" style="color:rgba(245,158,11,0.7);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.2em;margin-bottom:12px">
            <span style="display:inline-block;width:32px;height:1px;background:linear-gradient(to right,transparent,rgba(245,158,11,0.5))"></span>
            Editor's Choice
            <span style="display:inline-block;width:32px;height:1px;background:linear-gradient(to left,transparent,rgba(245,158,11,0.5))"></span>
        </div>
        <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;font-family:'Playfair Display',serif;color:#fff">Top Rated Casinos</h2>
        <p style="color:#6b7280;margin-top:12px;max-width:480px;margin-left:auto;margin-right:auto;font-size:14px">Hand-picked by our experts based on trust, game variety, bonuses, and player satisfaction.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
        @foreach($featuredCasinos->take(3) as $index => $casino)
        @php
            $gradients = [
                0 => ['top' => 'linear-gradient(135deg,rgba(245,158,11,0.15),rgba(180,120,30,0.05))', 'border' => 'rgba(245,158,11,0.4)', 'badge' => 'linear-gradient(135deg,#fbbf24,#f59e0b)', 'badgeText' => '#451a03', 'glow' => '0 0 60px rgba(245,158,11,0.1)', 'initial' => 'linear-gradient(135deg,#f59e0b,#d97706)', 'label' => '#1 GOLD'],
                1 => ['top' => 'linear-gradient(135deg,rgba(148,163,184,0.1),rgba(100,116,139,0.04))', 'border' => 'rgba(148,163,184,0.3)', 'badge' => 'linear-gradient(135deg,#cbd5e1,#94a3b8)', 'badgeText' => '#1e293b', 'glow' => '0 0 40px rgba(148,163,184,0.06)', 'initial' => 'linear-gradient(135deg,#94a3b8,#64748b)', 'label' => '#2 SILVER'],
                2 => ['top' => 'linear-gradient(135deg,rgba(180,83,9,0.1),rgba(154,52,18,0.04))', 'border' => 'rgba(180,83,9,0.3)', 'badge' => 'linear-gradient(135deg,#d97706,#b45309)', 'badgeText' => '#fff', 'glow' => '0 0 40px rgba(180,83,9,0.06)', 'initial' => 'linear-gradient(135deg,#d97706,#92400e)', 'label' => '#3 BRONZE'],
            ];
            $g = $gradients[$index];
        @endphp
        <a href="{{ route('casino.show', $casino->slug) }}"
           class="group relative flex flex-col overflow-hidden"
           style="background:rgba(15,15,25,0.8);border:1px solid {{ $g['border'] }};border-radius:24px;box-shadow:{{ $g['glow'] }};transition:all 0.4s ease;text-decoration:none{{ $index === 0 ? ';transform:scale(1.03)' : '' }}"
           onmouseover="this.style.boxShadow='0 20px 60px rgba(0,0,0,0.4),{{ str_replace('0.1','0.2',str_replace('0.06','0.15',$g['glow'])) }}';this.style.borderColor='{{ str_replace('0.3','0.6',str_replace('0.4','0.7',$g['border'])) }}'"
           onmouseout="this.style.boxShadow='{{ $g['glow'] }}';this.style.borderColor='{{ $g['border'] }}'">

            <div class="absolute inset-x-0 top-0" style="height:4px;background:{{ $g['badge'] }};opacity:0.6" aria-hidden="true"></div>
            <div class="absolute top-0 right-0 w-40 h-40 rounded-full blur-3xl pointer-events-none" style="background:{{ $g['top'] }};transform:translate(25%,-50%);opacity:0.7" aria-hidden="true"></div>

            <div class="relative flex flex-col flex-1" style="padding:28px 28px 0">
                <div class="flex items-start justify-between" style="margin-bottom:24px">
                    <div class="flex items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:12px;background:{{ $g['badge'] }};display:flex;align-items:center;justify-content:center;color:{{ $g['badgeText'] }};font-weight:800;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.2)">
                            #{{ $index + 1 }}
                        </div>
                        <span style="font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:rgba(156,163,175,0.6)">{{ $gradients[$index]['label'] }}</span>
                    </div>
                    @if($casino->average_rating)
                        <div style="display:flex;align-items:center;gap:6px;background:rgba(10,10,20,0.6);border:1px solid rgba(180,120,30,0.2);border-radius:999px;padding:6px 12px">
                            <span style="color:#fbbf24;font-size:14px">★</span>
                            <span style="color:#fff;font-weight:700;font-size:14px">{{ number_format($casino->average_rating, 1) }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-4" style="margin-bottom:20px">
                    @if($casino->logo_url)
                        <div style="width:64px;height:64px;border-radius:16px;background:rgba(30,30,45,0.8);border:1px solid rgba(180,120,30,0.2);padding:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                            <img src="{{ $casino->logo_url }}" alt="{{ $casino->logo_alt ?? $casino->name }}" width="64" height="64" style="width:100%;height:100%;object-fit:contain" loading="lazy">
                        </div>
                    @else
                        <div style="width:64px;height:64px;border-radius:16px;background:{{ $g['initial'] }};display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:rgba(255,255,255,0.9);font-family:'Playfair Display',serif;flex-shrink:0;box-shadow:0 8px 24px rgba(0,0,0,0.3)">
                            {{ mb_substr($casino->name, 0, 1) }}
                        </div>
                    @endif
                    <div style="min-width:0">
                        <h3 style="font-weight:700;color:#fff;font-size:18px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;transition:color 0.2s">{{ $casino->name }}</h3>
                        @if($casino->country)
                            <p style="font-size:13px;color:#6b7280;margin-top:4px;display:flex;align-items:center;gap:4px">
                                <svg style="width:12px;height:12px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $casino->country }}
                            </p>
                        @endif
                    </div>
                </div>

                @if($casino->short_description)
                    <p class="line-clamp-2" style="color:#9ca3af;font-size:13px;line-height:1.6;margin-bottom:20px">{{ $casino->short_description }}</p>
                @endif

                <div class="flex flex-wrap gap-2" style="margin-top:auto;margin-bottom:20px">
                    @if($casino->license)
                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:rgba(52,211,153,0.8);background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);padding:4px 10px;border-radius:8px">
                            <svg style="width:12px;height:12px" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Licensed
                        </span>
                    @endif
                    @if($casino->established_year)
                        <span style="font-size:11px;font-weight:500;color:#6b7280;background:rgba(30,30,45,0.6);border:1px solid rgba(180,120,30,0.12);padding:4px 10px;border-radius:8px">Est. {{ $casino->established_year }}</span>
                    @endif
                    @if(($casino->approved_reviews_count ?? 0) > 0)
                        <span style="font-size:11px;font-weight:500;color:#6b7280;background:rgba(30,30,45,0.6);border:1px solid rgba(180,120,30,0.12);padding:4px 10px;border-radius:8px">{{ $casino->approved_reviews_count }} {{ Str::plural('review', $casino->approved_reviews_count) }}</span>
                    @endif
                </div>
            </div>

            <div style="padding:16px 28px;border-top:1px solid rgba(180,120,30,0.12);background:rgba(5,5,15,0.4);display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:13px;font-weight:600;color:rgba(245,158,11,0.7);transition:color 0.2s">View full review</span>
                <svg style="width:16px;height:16px;color:rgba(180,120,30,0.5);transition:all 0.2s" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     ALL FEATURED CASINOS GRID
═══════════════════════════════════════════════ --}}
<section style="margin-bottom:96px">
    <div class="flex items-center gap-2.5" style="margin-bottom:32px">
        <div style="width:3px;height:24px;border-radius:999px;background:linear-gradient(to bottom,#f59e0b,#b45309);flex-shrink:0"></div>
        <h2 style="font-size:17px;font-weight:700;color:#fff;letter-spacing:0.01em">Featured Casinos</h2>
        <span class="hidden sm:inline-flex items-center" style="font-size:12px;color:rgba(245,158,11,0.7);background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);padding:3px 10px;border-radius:999px;font-weight:600;margin-left:4px">★ Top Rated</span>
        <a href="{{ route('search') }}" class="ml-auto flex items-center gap-1" style="color:rgba(251,191,36,0.6);font-size:14px;font-weight:500;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='rgba(251,191,36,0.6)'">
            View all
            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($featuredCasinos->count() > 3 ? $featuredCasinos->skip(3) : $featuredCasinos as $casino)
            <x-casino-card :casino="$casino" />
        @empty
            <div class="col-span-full flex flex-col items-center justify-center text-center" style="padding:80px 0">
                <div style="width:64px;height:64px;background:rgba(15,15,25,0.8);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;border:1px solid rgba(180,120,30,0.2)">
                    <svg style="width:28px;height:28px;color:rgba(180,120,30,0.4)" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p style="color:#4b5563;font-size:14px">No casinos yet. Import some via the admin panel.</p>
            </div>
        @endforelse
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════════════ --}}
<section style="margin-bottom:96px">
    <div class="text-center" style="margin-bottom:48px">
        <div class="inline-flex items-center gap-2" style="color:rgba(245,158,11,0.7);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.2em;margin-bottom:12px">
            <span style="display:inline-block;width:32px;height:1px;background:linear-gradient(to right,transparent,rgba(245,158,11,0.5))"></span>
            Simple Process
            <span style="display:inline-block;width:32px;height:1px;background:linear-gradient(to left,transparent,rgba(245,158,11,0.5))"></span>
        </div>
        <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;font-family:'Playfair Display',serif;color:#fff">How It Works</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
        <div class="hidden md:block absolute" style="top:40px;left:20%;right:20%;height:1px;background:linear-gradient(to right,transparent,rgba(245,158,11,0.25),transparent)" aria-hidden="true"></div>

        @php
        $steps = [
            ['num' => '01', 'title' => 'Search & Filter', 'desc' => 'Browse by country, game type, bonus offers or use our smart search to find casinos that match your preferences.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'],
            ['num' => '02', 'title' => 'Compare & Read', 'desc' => 'Read expert reviews, compare ratings across trust, games, support, payments, and check real player experiences.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
            ['num' => '03', 'title' => 'Play with Confidence', 'desc' => 'Choose a verified casino knowing you have all the information you need for a safe, enjoyable experience.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
        ];
        @endphp

        @foreach($steps as $step)
        <div class="relative text-center">
            <div style="width:56px;height:56px;border-radius:16px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;position:relative;z-index:10;transition:all 0.3s">
                <svg style="width:24px;height:24px;color:#fbbf24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    {!! $step['icon'] !!}
                </svg>
            </div>
            <div style="font-size:10px;font-weight:700;color:rgba(245,158,11,0.4);text-transform:uppercase;letter-spacing:0.15em;margin-bottom:8px">Step {{ $step['num'] }}</div>
            <h3 style="font-weight:700;color:#fff;font-size:18px;margin-bottom:8px">{{ $step['title'] }}</h3>
            <p style="color:#6b7280;font-size:14px;line-height:1.6;max-width:280px;margin:0 auto">{{ $step['desc'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     BROWSE BY COUNTRY
═══════════════════════════════════════════════ --}}
@if($countries->isNotEmpty())
<section style="margin-bottom:96px">
    <div class="flex items-center gap-2.5" style="margin-bottom:32px">
        <div style="width:3px;height:24px;border-radius:999px;background:linear-gradient(to bottom,#f59e0b,#b45309);flex-shrink:0"></div>
        <h2 style="font-size:17px;font-weight:700;color:#fff;letter-spacing:0.01em">Browse by Country</h2>
        <span style="color:#4b5563;font-size:12px;margin-left:4px">{{ $countries->count() }} countries</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @foreach($countries->take(18) as $c)
            <a href="{{ route('country.show', $c->country_slug) }}"
               style="display:flex;align-items:center;gap:10px;background:rgba(15,15,25,0.6);border:1px solid rgba(180,120,30,0.15);padding:12px 14px;border-radius:12px;text-decoration:none;transition:all 0.3s;overflow:hidden"
               onmouseover="this.style.background='rgba(245,158,11,0.08)';this.style.borderColor='rgba(245,158,11,0.35)'"
               onmouseout="this.style.background='rgba(15,15,25,0.6)';this.style.borderColor='rgba(180,120,30,0.15)'">
                <div style="width:32px;height:32px;border-radius:8px;background:rgba(30,30,45,0.8);border:1px solid rgba(180,120,30,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg style="width:14px;height:14px;color:rgba(217,119,6,0.5)" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span style="font-size:13px;font-weight:500;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;transition:color 0.2s">{{ $c->country }}</span>
            </a>
        @endforeach
    </div>

    @if($countries->count() > 18)
        <div class="text-center" style="margin-top:24px">
            <a href="{{ route('countries.index') }}" class="inline-flex items-center gap-2" style="color:rgba(251,191,36,0.6);font-size:14px;font-weight:500;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='rgba(251,191,36,0.6)'">
                View all {{ $countries->count() }} countries
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    @endif
</section>
@endif

{{-- ═══════════════════════════════════════════════
     WHY TRUST US
═══════════════════════════════════════════════ --}}
<section style="margin-bottom:96px">
    <div class="relative overflow-hidden" style="border-radius:24px;border:1px solid rgba(180,120,30,0.2)">
        <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(15,23,42,0.9),rgba(15,15,24,1),rgba(69,26,3,0.15))" aria-hidden="true"></div>
        <div class="absolute pointer-events-none" style="top:0;right:0;width:384px;height:384px;background:rgba(245,158,11,0.05);border-radius:50%;filter:blur(80px);transform:translate(33%,-33%)" aria-hidden="true"></div>
        <div class="absolute pointer-events-none" style="bottom:0;left:0;width:256px;height:256px;background:rgba(217,119,6,0.03);border-radius:50%;filter:blur(80px);transform:translate(-33%,33%)" aria-hidden="true"></div>

        <div class="relative" style="padding:48px;padding:clamp(32px,4vw,64px)">
            <div class="text-center" style="margin-bottom:48px">
                <div class="inline-flex items-center gap-2" style="color:rgba(245,158,11,0.7);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.2em;margin-bottom:12px">
                    <span style="display:inline-block;width:32px;height:1px;background:linear-gradient(to right,transparent,rgba(245,158,11,0.5))"></span>
                    Our Promise
                    <span style="display:inline-block;width:32px;height:1px;background:linear-gradient(to left,transparent,rgba(245,158,11,0.5))"></span>
                </div>
                <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;font-family:'Playfair Display',serif;color:#fff">Why Trust RoyalCasinoHub</h2>
                <p style="color:#6b7280;margin-top:12px;max-width:480px;margin-left:auto;margin-right:auto;font-size:14px">Every casino on our platform goes through a rigorous review process.</p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px">
                @php
                $trustFeatures = [
                    ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>', 'title' => 'Verified Legitimacy', 'desc' => 'Every casino is checked for valid licenses and regulatory compliance before listing.', 'stat' => '100%', 'statLabel' => 'Verified'],
                    ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>', 'title' => 'Honest Ratings', 'desc' => 'Real player reviews and expert analysis — no paid placements influence our scores.', 'stat' => '10K+', 'statLabel' => 'Reviews'],
                    ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'title' => 'Best Bonuses', 'desc' => 'We track the latest exclusive offers and welcome bonuses updated in real time.', 'stat' => 'Daily', 'statLabel' => 'Updated'],
                    ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'title' => 'Global Coverage', 'desc' => 'Browse casinos in 100+ countries with localized regulations and availability.', 'stat' => '100+', 'statLabel' => 'Countries'],
                ];
                @endphp
                @foreach($trustFeatures as $feat)
                    <div style="background:rgba(15,15,25,0.5);border:1px solid rgba(180,120,30,0.12);border-radius:16px;padding:24px;transition:all 0.3s"
                         onmouseover="this.style.borderColor='rgba(245,158,11,0.3)';this.style.background='rgba(15,15,25,0.7)'"
                         onmouseout="this.style.borderColor='rgba(180,120,30,0.12)';this.style.background='rgba(15,15,25,0.5)'">
                        <div class="flex items-center justify-between" style="margin-bottom:20px">
                            <div style="width:48px;height:48px;border-radius:14px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <svg style="width:24px;height:24px;color:#fbbf24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    {!! $feat['icon'] !!}
                                </svg>
                            </div>
                            <div style="text-align:right">
                                <div class="gold-gradient-text" style="font-size:20px;font-weight:700;font-family:'Playfair Display',serif">{{ $feat['stat'] }}</div>
                                <div style="font-size:9px;color:#4b5563;text-transform:uppercase;letter-spacing:0.1em">{{ $feat['statLabel'] }}</div>
                            </div>
                        </div>
                        <h3 style="font-weight:600;color:#fff;font-size:14px;margin-bottom:8px">{{ $feat['title'] }}</h3>
                        <p style="color:#6b7280;font-size:12px;line-height:1.6">{{ $feat['desc'] }}</p>
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
<section style="margin-bottom:96px">
    <div class="flex items-center gap-2.5" style="margin-bottom:32px">
        <div style="width:3px;height:24px;border-radius:999px;background:linear-gradient(to bottom,#f59e0b,#b45309);flex-shrink:0"></div>
        <h2 style="font-size:17px;font-weight:700;color:#fff;letter-spacing:0.01em">Latest Player Reviews</h2>
        <a href="{{ route('reviews.index') }}" class="ml-auto flex items-center gap-1" style="color:rgba(251,191,36,0.6);font-size:14px;font-weight:500;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='rgba(251,191,36,0.6)'">
            All reviews
            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($latestReviews as $review)
            <div style="background:rgba(15,15,25,0.6);border:1px solid rgba(180,120,30,0.15);border-radius:16px;overflow:hidden;transition:all 0.3s"
                 onmouseover="this.style.borderColor='rgba(245,158,11,0.35)';this.style.boxShadow='0 10px 40px rgba(0,0,0,0.2)'"
                 onmouseout="this.style.borderColor='rgba(180,120,30,0.15)';this.style.boxShadow='none'">
                <div style="height:3px;background:linear-gradient(to right,transparent,rgba(245,158,11,0.15),transparent)"></div>
                <div style="padding:24px">
                    <div class="flex items-center gap-3" style="margin-bottom:16px">
                        <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,rgba(245,158,11,0.2),rgba(180,120,30,0.3));border:1px solid rgba(180,120,30,0.3);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fbbf24;flex-shrink:0">
                            {{ $review->user ? mb_substr($review->user->name, 0, 1) : '?' }}
                        </div>
                        <div style="min-width:0;flex:1">
                            <div style="font-size:14px;font-weight:500;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $review->user?->name ?? 'Anonymous' }}</div>
                            <div style="font-size:11px;color:#4b5563">{{ $review->created_at->diffForHumans() }}</div>
                        </div>
                        <div style="flex-shrink:0;color:rgba(251,191,36,0.9)">
                            <x-star-rating :rating="$review->rating" />
                        </div>
                    </div>

                    <a href="{{ route('casino.show', $review->casino->slug) }}"
                       style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:rgba(245,158,11,0.7);text-decoration:none;text-transform:uppercase;letter-spacing:0.12em;margin-bottom:12px;transition:color 0.2s"
                       onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='rgba(245,158,11,0.7)'">
                        <svg style="width:12px;height:12px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $review->casino->name }}
                    </a>

                    @if($review->title)
                        <p style="color:#fff;font-weight:600;font-size:14px;margin-bottom:8px;line-height:1.4">{{ $review->title }}</p>
                    @endif
                    <p style="color:#6b7280;font-size:13px;line-height:1.6">{{ Str::limit($review->content, 140) }}</p>
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
<section style="margin-bottom:40px">
    <div class="relative overflow-hidden" style="border-radius:24px;border:1px solid rgba(180,120,30,0.2)">
        <div class="absolute inset-0" style="background:linear-gradient(to right,rgba(69,26,3,0.3),rgba(15,15,24,1),rgba(69,26,3,0.3))" aria-hidden="true"></div>
        <div class="absolute pointer-events-none" style="top:0;left:50%;transform:translateX(-50%) translateY(-66%);width:500px;height:500px;background:rgba(245,158,11,0.06);border-radius:50%;filter:blur(80px)" aria-hidden="true"></div>

        <div class="relative text-center" style="padding:64px 32px">
            <div style="width:64px;height:64px;border-radius:16px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);display:inline-flex;align-items:center;justify-content:center;margin-bottom:24px">
                <svg style="width:32px;height:32px;color:#fbbf24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h2 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:700;font-family:'Playfair Display',serif;color:#fff;margin-bottom:12px">Join Our Community</h2>
            <p style="color:#9ca3af;font-size:14px;max-width:420px;margin:0 auto 32px">Create an account to write reviews, save favorites, and get personalized casino recommendations.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                   style="display:inline-block;background:linear-gradient(135deg,#f59e0b,#d97706);color:#451a03;font-weight:700;padding:14px 32px;border-radius:12px;font-size:14px;letter-spacing:0.02em;text-decoration:none;transition:all 0.2s;box-shadow:0 4px 20px rgba(245,158,11,0.3)"
                   onmouseover="this.style.background='linear-gradient(135deg,#fbbf24,#f59e0b)';this.style.boxShadow='0 8px 30px rgba(245,158,11,0.4)'"
                   onmouseout="this.style.background='linear-gradient(135deg,#f59e0b,#d97706)';this.style.boxShadow='0 4px 20px rgba(245,158,11,0.3)'">
                    Create Free Account
                </a>
                <a href="{{ route('login') }}" style="color:#9ca3af;font-size:14px;font-weight:500;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='#9ca3af'">
                    Already have an account? Sign in
                </a>
            </div>
        </div>
    </div>
</section>
@endguest

@endsection
