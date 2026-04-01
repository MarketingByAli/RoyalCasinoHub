@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto px-1 sm:px-0">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-amber-400 font-serif tracking-tight">Create an account</h1>
        <p class="mt-2 text-sm text-gray-500">Join RoyalCasinoHub to review casinos or list your venue.</p>
    </div>

    <div class="rounded-2xl border border-amber-900/25 bg-slate-900/50 backdrop-blur-sm shadow-xl shadow-black/20 p-6 sm:p-8">
        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label for="reg-name" class="block text-sm font-medium text-gray-300">Full name</label>
                <input id="reg-name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white placeholder-gray-600 shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500/50 transition-shadow @error('name') border-red-500/70 focus:ring-red-500/30 @enderror">
                @error('name')
                    <p class="text-red-400 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <fieldset class="space-y-3">
                <legend class="block text-sm font-medium text-gray-300 mb-1">Account type</legend>
                <p class="text-xs text-gray-500 -mt-1 mb-2">Choose how you plan to use the site.</p>
                <div class="grid gap-3">
                    <label class="relative flex gap-3 p-4 rounded-xl border cursor-pointer transition-all duration-150 {{ old('account_type', 'user') === 'user' ? 'border-amber-500/55 bg-amber-500/[0.07] ring-1 ring-amber-500/25' : 'border-amber-900/35 bg-slate-950/40 hover:border-amber-800/50 hover:bg-slate-950/60' }}">
                        <input type="radio" name="account_type" value="user" class="mt-0.5 h-4 w-4 shrink-0 border-amber-800/50 bg-slate-900 text-amber-500 focus:ring-amber-500/50 focus:ring-offset-0 focus:ring-offset-slate-900" {{ old('account_type', 'user') === 'user' ? 'checked' : '' }}>
                        <span class="min-w-0">
                            <span class="block text-white font-medium">Player / reviewer</span>
                            <span class="block text-sm text-gray-500 mt-1 leading-snug">Write reviews, save favorites, and claim a listing later.</span>
                        </span>
                    </label>
                    <label class="relative flex gap-3 p-4 rounded-xl border cursor-pointer transition-all duration-150 {{ old('account_type') === 'casino_owner' ? 'border-amber-500/55 bg-amber-500/[0.07] ring-1 ring-amber-500/25' : 'border-amber-900/35 bg-slate-950/40 hover:border-amber-800/50 hover:bg-slate-950/60' }}">
                        <input type="radio" name="account_type" value="casino_owner" class="mt-0.5 h-4 w-4 shrink-0 border-amber-800/50 bg-slate-900 text-amber-500 focus:ring-amber-500/50 focus:ring-offset-0 focus:ring-offset-slate-900" {{ old('account_type') === 'casino_owner' ? 'checked' : '' }}>
                        <span class="min-w-0">
                            <span class="block text-white font-medium">Casino owner / operator</span>
                            <span class="block text-sm text-gray-500 mt-1 leading-snug">Submit new listings for your venues and manage claimed listings after email verification.</span>
                        </span>
                    </label>
                </div>
                @error('account_type')
                    <p class="text-red-400 text-sm">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="space-y-2">
                <label for="reg-email" class="block text-sm font-medium text-gray-300">Email</label>
                <input id="reg-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                    class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white placeholder-gray-600 shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500/50 transition-shadow @error('email') border-red-500/70 focus:ring-red-500/30 @enderror">
                @error('email')
                    <p class="text-red-400 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="reg-password" class="block text-sm font-medium text-gray-300">Password</label>
                    <input id="reg-password" type="password" name="password" required autocomplete="new-password"
                        class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500/50 transition-shadow @error('password') border-red-500/70 focus:ring-red-500/30 @enderror">
                    @error('password')
                        <p class="text-red-400 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for="reg-password-confirm" class="block text-sm font-medium text-gray-300">Confirm password</label>
                    <input id="reg-password-confirm" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500/50 transition-shadow">
                </div>
            </div>

            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold py-3 rounded-xl shadow-lg shadow-amber-900/20 transition-colors">
                Create account
            </button>

            {{-- Honeypot: fixed + minimal box so it never overlaps real fields (absolute inside relative was stacking on Name) --}}
            <div class="fixed top-0 left-0 w-px h-px overflow-hidden opacity-0 pointer-events-none" aria-hidden="true">
                <label for="reg-company-website">Company website</label>
                <input id="reg-company-website" type="text" name="company_website" tabindex="-1" autocomplete="off" value="">
            </div>
        </form>
    </div>

    <p class="mt-6 text-center text-gray-400 text-sm">
        Already have an account?
        <a href="{{ route('login') }}" class="text-amber-400 hover:text-amber-300 font-medium hover:underline underline-offset-2">Log in</a>
    </p>
</div>
@endsection
