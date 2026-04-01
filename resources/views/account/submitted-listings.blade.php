@extends('layouts.app')

@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-2">My submitted listings</h1>
    <p class="text-gray-500 text-sm mb-6">Casinos you proposed. Pending listings are not public until payment and approval.</p>

    @if($casinos->isEmpty())
        <p class="text-gray-400 text-sm mb-6">You have not submitted any listings yet.</p>
        @can('create', App\Models\Casino::class)
            <a href="{{ route('casino-listings.create') }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-6 py-2 rounded-lg">Submit a casino</a>
        @endcan
    @else
        <ul class="space-y-3">
            @foreach($casinos as $casino)
                <li class="bg-slate-900/60 border border-amber-900/20 rounded-xl px-4 py-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="font-medium text-white">{{ $casino->name }}</p>
                        <p class="text-xs text-gray-500">{{ $casino->country }} · submitted {{ $casino->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg
                        @if($casino->status === 'published') bg-emerald-500/15 text-emerald-400 border border-emerald-500/30
                        @elseif($casino->status === 'pending') bg-amber-500/15 text-amber-300 border border-amber-500/30
                        @else bg-slate-700 text-gray-300 border border-slate-600 @endif">{{ ucfirst($casino->status) }}</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-6">
            {{ $casinos->links() }}
        </div>
    @endif
</div>
@endsection
