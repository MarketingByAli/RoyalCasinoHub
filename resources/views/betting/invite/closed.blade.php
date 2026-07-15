@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 mb-2">{{ $market->title }}</h1>
    <p class="text-gray-500 mb-6">This challenge is no longer open ({{ str_replace('_', ' ', $market->status->value) }}).</p>
    @auth
        @can('view', $market)
            <a href="{{ route('betting.challenges.show', $market) }}" class="text-amber-400 hover:underline">View details</a>
        @endauth
    @else
        <a href="{{ route('login') }}" class="text-amber-400 hover:underline">Log in</a>
    @endauth
</div>
@endsection
