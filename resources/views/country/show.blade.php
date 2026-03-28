@extends('layouts.app')

@section('content')
<nav class="mb-6 text-sm" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Home</a></li>
        <li class="text-gray-600">/</li>
        <li class="text-amber-400">{{ $country }}</li>
    </ol>
</nav>

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-10">
    <div>
        <h1 class="text-3xl md:text-4xl font-bold text-white font-serif mb-2">Best Online Casinos in <span class="text-amber-400">{{ $country }}</span></h1>
        <p class="text-gray-500">Updated {{ now()->format('F Y') }}</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <label class="text-sm text-gray-400">Sort</label>
        <select name="sort" onchange="this.form.submit()" class="bg-slate-900/80 border border-amber-900/20 rounded-lg px-3 py-2 text-sm text-white">
            <option value="name" {{ ($sort ?? 'name') === 'name' ? 'selected' : '' }}>Name</option>
            <option value="top-rated" {{ ($sort ?? '') === 'top-rated' ? 'selected' : '' }}>Top rated</option>
            <option value="most-reviewed" {{ ($sort ?? '') === 'most-reviewed' ? 'selected' : '' }}>Most reviewed</option>
            <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Newest</option>
        </select>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @foreach($casinos as $casino)
        <x-casino-card :casino="$casino" />
    @endforeach
</div>

<div class="mt-10">
    {{ $casinos->withQueryString()->links() }}
</div>
@endsection
