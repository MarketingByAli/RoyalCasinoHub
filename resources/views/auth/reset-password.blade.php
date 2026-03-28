@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-6">Set new password</h1>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">New password</label>
            <input type="password" name="password" required autocomplete="new-password"
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Confirm password</label>
            <input type="password" name="password_confirmation" required autocomplete="new-password"
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white">
        </div>
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold py-2 rounded-lg">Reset password</button>
    </form>
</div>
@endsection
