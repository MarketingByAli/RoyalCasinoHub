@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto relative">
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-6">Register</h1>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div class="absolute -left-[9999px] opacity-0 pointer-events-none" aria-hidden="true">
            <label>Company website</label>
            <input type="text" name="company_website" tabindex="-1" autocomplete="off" value="">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('name') border-red-500 @enderror">
            @error('name')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-2">Account type</label>
            <div class="space-y-2">
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors {{ old('account_type', 'user') === 'user' ? 'border-amber-500/50 bg-amber-500/5' : 'border-amber-900/30 bg-slate-800/30 hover:border-amber-900/50' }}">
                    <input type="radio" name="account_type" value="user" class="mt-1 text-amber-500" {{ old('account_type', 'user') === 'user' ? 'checked' : '' }}>
                    <span>
                        <span class="text-white font-medium">Player / reviewer</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Write reviews, save favorites, and claim a listing later.</span>
                    </span>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors {{ old('account_type') === 'casino_owner' ? 'border-amber-500/50 bg-amber-500/5' : 'border-amber-900/30 bg-slate-800/30 hover:border-amber-900/50' }}">
                    <input type="radio" name="account_type" value="casino_owner" class="mt-1 text-amber-500" {{ old('account_type') === 'casino_owner' ? 'checked' : '' }}>
                    <span>
                        <span class="text-white font-medium">Casino owner / operator</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Submit new listings for your venues and manage claimed listings after verification.</span>
                    </span>
                </label>
            </div>
            @error('account_type')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Password</label>
            <input type="password" name="password" required
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white">
        </div>
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold py-2 rounded-lg">Register</button>
    </form>
    <p class="mt-4 text-gray-400 text-sm">Already have an account? <a href="{{ route('login') }}" class="text-amber-400 hover:underline">Log in</a></p>
</div>
@endsection
