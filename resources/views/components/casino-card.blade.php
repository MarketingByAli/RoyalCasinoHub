@props(['casino'])

<a href="{{ route('casino.show', $casino->slug) }}"
   class="group relative flex flex-col bg-slate-900/70 border border-amber-900/20 rounded-2xl overflow-hidden hover:border-amber-500/50 hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-300">

    {{-- Top shimmer line --}}
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/30 to-transparent group-hover:via-amber-400/70 transition-all duration-500" aria-hidden="true"></div>

    {{-- Hover glow overlay --}}
    <div class="absolute inset-0 bg-gradient-to-b from-amber-500/0 via-transparent to-amber-500/0 group-hover:from-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none" aria-hidden="true"></div>

    <div class="relative p-6 flex flex-col flex-1">

        {{-- Logo + name row --}}
        <div class="flex items-center gap-4 mb-4">
            @if($casino->logo_url)
                <div class="w-14 h-14 rounded-xl bg-slate-800/80 border border-amber-900/20 p-1.5 flex items-center justify-center overflow-hidden flex-shrink-0 group-hover:border-amber-500/30 transition-colors">
                    <img src="{{ $casino->logo_url }}"
                         alt="{{ $casino->logo_alt ?? $casino->name }}"
                         width="56" height="56"
                         class="w-full h-full object-contain"
                         loading="lazy">
                </div>
            @else
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500/20 to-amber-900/20 border border-amber-900/30 flex items-center justify-center text-xl font-bold text-amber-400 font-serif flex-shrink-0">
                    {{ substr($casino->name, 0, 1) }}
                </div>
            @endif

            <div class="min-w-0">
                <h2 class="font-bold text-white group-hover:text-amber-300 transition-colors duration-200 leading-tight truncate text-sm">{{ $casino->name }}</h2>
                @php
                    $loc = collect([$casino->locality, $casino->region, $casino->country])->filter()->first();
                @endphp
                @if($loc)
                    <p class="text-xs text-gray-600 mt-0.5 truncate">{{ $loc }}</p>
                @endif
            </div>
        </div>

        {{-- Rating --}}
        @if($casino->average_rating)
            <div class="flex items-center gap-2 mb-4">
                <div class="text-amber-400/90">
                    <x-star-rating :rating="$casino->average_rating" />
                </div>
                <span class="text-xs text-gray-600">({{ $casino->approved_reviews_count ?? $casino->reviews_count ?? 0 }})</span>
            </div>
        @endif

        {{-- Meta chips --}}
        <div class="flex flex-wrap gap-1.5 mt-auto">
            @if($casino->established_year)
                <span class="text-[10px] font-medium text-gray-500 bg-slate-800/60 border border-amber-900/15 px-2 py-0.5 rounded-md">Est. {{ $casino->established_year }}</span>
            @endif
            @if($casino->country)
                <span class="text-[10px] font-medium text-gray-500 bg-slate-800/60 border border-amber-900/15 px-2 py-0.5 rounded-md">{{ $casino->country }}</span>
            @endif
        </div>
    </div>

    {{-- Bottom action bar --}}
    <div class="px-6 py-3 border-t border-amber-900/15 bg-slate-950/40 flex items-center justify-between">
        <span class="text-xs text-gray-600 group-hover:text-gray-500 transition-colors">View details</span>
        <svg class="w-3.5 h-3.5 text-amber-700/60 group-hover:text-amber-400 group-hover:translate-x-0.5 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
</a>
