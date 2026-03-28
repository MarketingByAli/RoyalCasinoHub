@extends('layouts.app')

@section('content')
<div class="max-w-5xl">
    <nav class="mb-6 text-sm text-gray-500">
        <a href="{{ route('account.index') }}" class="hover:text-amber-400">Account</a>
        <span class="text-gray-600"> / </span>
        <span class="text-amber-400">Saved casinos</span>
    </nav>
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-8">Saved casinos</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($casinos as $casino)
            <x-casino-card :casino="$casino" />
        @empty
            <p class="text-gray-500 col-span-full">You have not saved any casinos yet.</p>
        @endforelse
    </div>
    <div class="mt-10">{{ $casinos->links() }}</div>
</div>
@endsection
