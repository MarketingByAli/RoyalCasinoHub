@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 font-serif mb-8">My Claimed Listings</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($casinos as $casino)
        <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
            @if($casino->logo_url)
                <img src="{{ $casino->logo_url }}" alt="{{ $casino->logo_alt ?? $casino->name }}" width="80" height="80" class="w-20 h-20 object-contain mb-4 rounded-lg" loading="lazy">
            @endif
            <h2 class="font-semibold text-white">{{ $casino->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $casino->country }}</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('casino-owner.analytics', $casino) }}" class="text-amber-400/90 hover:text-amber-300 text-sm">Analytics</a>
                <a href="{{ route('casino-owner.edit', $casino) }}" class="text-amber-400 hover:text-amber-300 text-sm">Edit listing →</a>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-8">{{ $casinos->links() }}</div>
@endsection
