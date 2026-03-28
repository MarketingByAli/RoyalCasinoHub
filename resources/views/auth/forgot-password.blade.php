@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-2">Forgot password</h1>
    <p class="text-gray-500 text-sm mb-6">Enter your email and we will send a reset link.</p>
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-gray-400 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold py-2 rounded-lg">Send reset link</button>
    </form>
    <p class="mt-4 text-gray-400 text-sm"><a href="{{ route('login') }}" class="text-amber-400 hover:underline">Back to log in</a></p>
</div>
@endsection
