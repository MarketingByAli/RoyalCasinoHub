@extends('layouts.admin')

@section('content')
<div class="max-w-4xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-amber-400">Deposit methods</h1>
        <a href="{{ route('admin.betting.deposit-methods.create') }}" class="bg-amber-500 text-amber-950 font-semibold px-4 py-2 rounded-lg">Add wallet</a>
    </div>
    <p class="text-gray-500 text-sm mb-6">These addresses and QR codes appear on the user Wallet → Add funds screen.</p>

    <div class="space-y-3">
        @forelse($methods as $method)
            <div class="p-4 rounded-lg border border-amber-900/30 bg-slate-900/40 flex flex-wrap gap-4 justify-between items-center">
                <div class="flex gap-4 items-center">
                    @if($method->qrUrl())
                        <img src="{{ $method->qrUrl() }}" alt="" class="w-14 h-14 rounded bg-white p-1 object-contain">
                    @endif
                    <div>
                        <p class="text-white font-medium">{{ $method->displayLabel() }}
                            @unless($method->is_active)<span class="text-xs text-red-400 ml-2">inactive</span>@endunless
                        </p>
                        <p class="text-xs text-gray-500 break-all">{{ $method->address }}</p>
                    </div>
                </div>
                <div class="flex gap-3 text-sm">
                    <a href="{{ route('admin.betting.deposit-methods.edit', $method) }}" class="text-amber-400">Edit</a>
                    <form method="POST" action="{{ route('admin.betting.deposit-methods.destroy', $method) }}" onsubmit="return confirm('Delete this deposit method?')">
                        @csrf @method('DELETE')
                        <button class="text-red-400">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No deposit methods yet.</p>
        @endforelse
    </div>
</div>
@endsection
