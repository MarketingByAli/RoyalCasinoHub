@extends('layouts.app')

@section('content')
<nav class="mb-6 text-sm" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Home</a></li>
        <li class="text-gray-600">/</li>
        <li class="text-amber-400">{{ $query ? "Search: {$query}" : 'Search' }}</li>
    </ol>
</nav>

<h1 class="text-3xl md:text-4xl font-bold text-white font-serif mb-2">
    @if($query)
        Search: <span class="text-amber-400">"{{ $query }}"</span>
    @else
        Search Casinos
    @endif
</h1>
<p class="text-gray-500 mb-8">Find trusted online casino reviews and ratings.</p>

@if($query && strlen($query) < 2)
    <p class="text-gray-400">Enter at least 2 characters to search.</p>
@elseif($query)
    @if($casinos->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($casinos as $casino)
                <x-casino-card :casino="$casino" />
            @endforeach
        </div>
        @if($casinos instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-10">{{ $casinos->withQueryString()->links() }}</div>
        @endif
    @else
        <div class="bg-slate-900/60 border border-amber-900/20 rounded-xl p-8 text-center">
            <p class="text-gray-400">No casinos found for "{{ $query }}".</p>
            <a href="{{ route('search') }}" class="inline-block mt-4 text-amber-400 hover:text-amber-300 font-medium transition-colors">Try a different search</a>
        </div>
    @endif
@else
    <form action="{{ route('search') }}" method="GET">
        <div class="flex gap-3 max-w-xl">
            <input type="search" name="q" placeholder="Search by casino name..." class="flex-1 bg-slate-900/80 border border-amber-900/20 rounded-xl px-5 py-3.5 text-white placeholder-gray-500 focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 focus:outline-none transition-all">
            <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-8 py-3.5 rounded-xl transition-all hover:shadow-lg hover:shadow-amber-500/25">Search</button>
        </div>
    </form>
@endif
@endsection
