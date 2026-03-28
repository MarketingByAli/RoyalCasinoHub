@extends('layouts.app')

@section('content')
<div class="max-w-xl">
    <nav class="mb-6 text-sm text-gray-500">
        <a href="{{ route('account.index') }}" class="hover:text-amber-400">Account</a>
        <span class="mx-2">/</span>
        <span class="text-gray-400">Profile</span>
    </nav>
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-6">Profile</h1>

    <div class="bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 mb-8">
        <h2 class="font-semibold text-white mb-4">Display name</h2>
        <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm text-gray-400 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                    class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-4 py-2 text-white @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <p class="text-xs text-gray-500">Email: {{ auth()->user()->email }} (contact support to change)</p>
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-4 py-2 rounded-lg text-sm">Save name</button>
        </form>
    </div>

    <div class="bg-slate-900/60 border border-amber-900/20 rounded-xl p-6">
        <h2 class="font-semibold text-white mb-4">Change password</h2>
        <form method="POST" action="{{ route('account.password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm text-gray-400 mb-1">Current password</label>
                <input type="password" name="current_password" required autocomplete="current-password"
                    class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-4 py-2 text-white @error('current_password') border-red-500 @enderror">
                @error('current_password') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">New password</label>
                <input type="password" name="password" required autocomplete="new-password"
                    class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-4 py-2 text-white @error('password') border-red-500 @enderror">
                @error('password') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Confirm new password</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-4 py-2 text-white">
            </div>
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-4 py-2 rounded-lg text-sm">Update password</button>
        </form>
    </div>
</div>
@endsection
