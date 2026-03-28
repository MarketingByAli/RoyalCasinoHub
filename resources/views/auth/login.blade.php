@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-6">Log In</h1>
    @if(session('success'))
        <div class="mb-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-gray-400 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
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
        <div class="flex items-center">
            <input type="checkbox" name="remember" id="remember" class="rounded border-amber-900/30">
            <label for="remember" class="ml-2 text-sm text-gray-400">Remember me</label>
        </div>
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold py-2 rounded-lg">Log In</button>
    </form>
    <p class="mt-4 text-gray-400 text-sm"><a href="{{ route('password.request') }}" class="text-amber-400 hover:underline">Forgot your password?</a></p>
    <p class="mt-2 text-gray-400 text-sm">Don't have an account? <a href="{{ route('register') }}" class="text-amber-400 hover:underline">Register</a></p>
</div>
@endsection
