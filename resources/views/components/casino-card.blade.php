@props(['casino'])

<a href="{{ route('casino.show', $casino->slug) }}" class="group relative bg-slate-900/60 border border-amber-900/20 rounded-2xl p-6 hover:border-amber-500/40 hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-300 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-amber-500/0 to-amber-500/0 group-hover:from-amber-500/5 group-hover:to-transparent transition-colors duration-300" aria-hidden="true"></div>
    <div class="relative">
        @if($casino->logo_url)
            <div class="w-16 h-16 mb-4 rounded-xl bg-slate-800/80 p-2 flex items-center justify-center overflow-hidden">
                <img src="{{ $casino->logo_url }}" alt="{{ $casino->logo_alt ?? $casino->name }}" width="64" height="64" class="w-full h-full object-contain" loading="lazy">
            </div>
        @else
            <div class="w-16 h-16 rounded-xl bg-slate-800/80 mb-4 flex items-center justify-center text-xl font-bold text-amber-500/90">{{ substr($casino->name, 0, 1) }}</div>
        @endif
        <h2 class="font-semibold text-white group-hover:text-amber-400 transition-colors duration-200">{{ $casino->name }}</h2>
        @php
            $cardLoc = collect([$casino->locality, $casino->region])->filter()->implode(', ');
        @endphp
        <p class="text-sm text-gray-500 mt-1">{{ $casino->country }}@if($cardLoc)<span class="text-gray-600"> · </span>{{ $cardLoc }}@endif@if($casino->established_year)<span class="text-gray-600"> · </span>Founded {{ $casino->established_year }}@endif</p>
        @if($casino->average_rating)
            <div class="mt-3 flex items-center gap-1.5 text-amber-400/90">
                <x-star-rating :rating="$casino->average_rating" />
                <span class="text-gray-500 text-xs ml-1">({{ $casino->approved_reviews_count ?? $casino->reviews_count ?? 0 }})</span>
            </div>
        @endif
    </div>
</a>
