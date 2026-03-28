@extends('layouts.app')

@section('content')
<nav class="mb-6 text-sm" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Home</a></li>
        <li class="text-gray-600">/</li>
        <li class="text-amber-400">Browse by tag</li>
    </ol>
</nav>

<h1 class="text-3xl md:text-4xl font-bold text-white font-serif mb-2">Browse by tag</h1>
<p class="text-gray-500 mb-10">Filter casinos by category. Tags are assigned in admin.</p>

@if($tags->isEmpty())
    <p class="text-gray-500">No tags with published casinos yet.</p>
@else
    <ul class="flex flex-wrap gap-3">
        @foreach($tags as $tag)
            <li>
                <a href="{{ route('browse.tag', $tag->slug) }}" class="inline-flex items-center gap-2 bg-slate-900/60 border border-amber-900/20 hover:border-amber-500/40 rounded-xl px-4 py-2 text-amber-400 transition-colors">
                    {{ $tag->name }}
                    <span class="text-xs text-gray-500">({{ $tag->casinos_count }})</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
@endsection
