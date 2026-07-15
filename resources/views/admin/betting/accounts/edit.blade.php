@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-2">Betting account</h1>
<p class="text-gray-500 mb-6">User #{{ $user->id }} · {{ $user->email }} · {{ $user->bettingProfile?->username ?? 'no username' }}</p>

@if(! $user->bettingProfile)
    <p class="text-red-400">This user has no betting profile.</p>
@else
    <form method="POST" action="{{ route('admin.betting.accounts.update', $user) }}" class="max-w-md space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm text-gray-400 mb-1">Account state</label>
            <select name="account_state" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
                @foreach($states as $state)
                    <option value="{{ $state->value }}" @selected(old('account_state', $user->bettingProfile->account_state->value) === $state->value)>
                        {{ $state->value }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-amber-500 text-amber-950 px-4 py-2 rounded font-semibold">Update state</button>
    </form>
    <p class="text-gray-500 text-sm mt-4">
        <a href="{{ route('admin.betting.wallets.index', ['user_id' => $user->id]) }}" class="text-amber-400 hover:underline">Adjust wallet</a>
    </p>
@endif
@endsection
