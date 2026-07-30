@extends('layouts.admin')

@section('content')
@php $editing = $method->exists; @endphp
<div class="max-w-xl">
    <h1 class="text-2xl font-bold text-amber-400 mb-6">{{ $editing ? 'Edit deposit method' : 'Add deposit wallet' }}</h1>
    <form method="POST" enctype="multipart/form-data"
        action="{{ $editing ? route('admin.betting.deposit-methods.update', $method) : route('admin.betting.deposit-methods.store') }}"
        class="space-y-4">
        @csrf
        @if($editing) @method('PUT') @endif

        <div>
            <label class="block text-sm text-gray-400 mb-1">Coin name</label>
            <input name="coin_name" value="{{ old('coin_name', $method->coin_name) }}" required placeholder="USDT" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Network</label>
            <input name="network" value="{{ old('network', $method->network) }}" placeholder="TRC20 / ERC20 / Bitcoin" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Wallet address</label>
            <input name="address" value="{{ old('address', $method->address) }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Explanation / instructions for users</label>
            <textarea name="instructions" rows="4" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2" placeholder="Only send USDT on TRC20. Wrong network may result in lost funds.">{{ old('instructions', $method->instructions) }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">QR code image</label>
            @if($method->qrUrl())
                <img src="{{ $method->qrUrl() }}" alt="Current QR" class="w-32 h-32 mb-2 rounded bg-white p-2 object-contain">
            @endif
            <input type="file" name="qr_code" accept="image/*" class="w-full text-sm text-gray-400">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Sort order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $method->sort_order ?? 0) }}" min="0" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
        </div>
        <label class="flex gap-2 text-sm text-gray-300">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method->is_active ?? true))>
            Active (show on user wallet)
        </label>
        <button class="bg-amber-500 text-amber-950 font-semibold px-4 py-2 rounded-lg">{{ $editing ? 'Save changes' : 'Create' }}</button>
    </form>
</div>
@endsection
