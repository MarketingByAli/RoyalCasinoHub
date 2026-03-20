@extends('layouts.app')

@section('content')
<nav class="mb-6 text-sm" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Home</a></li>
        <li class="text-gray-600">/</li>
        <li class="text-amber-400">{{ $country }}</li>
    </ol>
</nav>

<h1 class="text-3xl md:text-4xl font-bold text-white font-serif mb-2">Best Online Casinos in <span class="text-amber-400">{{ $country }}</span></h1>
<p class="text-gray-500 mb-10">Updated {{ now()->format('F Y') }}</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @foreach($casinos as $casino)
        <x-casino-card :casino="$casino" />
    @endforeach
</div>

<div class="mt-10">
    {{ $casinos->withQueryString()->links() }}
</div>
@endsection
