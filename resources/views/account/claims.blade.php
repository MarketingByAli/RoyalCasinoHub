@extends('layouts.app')

@section('content')
<div class="max-w-3xl">
    <nav class="mb-6 text-sm text-gray-500">
        <a href="{{ route('account.index') }}" class="hover:text-amber-400">Account</a>
        <span class="mx-2">/</span>
        <span class="text-gray-400">My claims</span>
    </nav>
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-6">My claims</h1>
    @forelse($claims as $claim)
        <div class="bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 mb-4">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <a href="{{ route('casino.show', $claim->casino->slug) }}" class="font-semibold text-white hover:text-amber-400">{{ $claim->casino->name }}</a>
                    @if($claim->submitted_at)
                        <p class="text-xs text-gray-500 mt-1">Submitted {{ $claim->submitted_at->format('M j, Y') }}</p>
                    @endif
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded
                    @if($claim->status === 'approved') bg-emerald-500/20 text-emerald-400
                    @elseif($claim->status === 'rejected') bg-red-500/20 text-red-400
                    @else bg-amber-500/20 text-amber-400 @endif">{{ ucfirst($claim->status) }}</span>
            </div>
            @if($claim->status === 'rejected' && $claim->notes)
                <p class="text-sm text-gray-400 mt-3 border-t border-amber-900/20 pt-3">{{ $claim->notes }}</p>
            @endif
        </div>
    @empty
        <p class="text-gray-500">You have not submitted any listing claims.</p>
    @endforelse
    <div class="mt-6">{{ $claims->links() }}</div>
</div>
@endsection
