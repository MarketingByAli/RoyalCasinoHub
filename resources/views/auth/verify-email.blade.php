@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-2">Verify your email</h1>
    <p class="text-gray-400 text-sm mb-6">Thanks for signing up. Before you continue, please verify your email by clicking the link we sent you. If you did not receive the email, we can send another.</p>
    @if(session('status') == 'verification-link-sent')
        <p class="text-emerald-400 text-sm mb-4">A new verification link has been sent to your email address.</p>
    @endif
    <form method="POST" action="{{ route('verification.send') }}" class="inline">
        @csrf
        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-4 py-2 rounded-lg text-sm">Resend verification email</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="inline ml-2">
        @csrf
        <button type="submit" class="text-gray-500 hover:text-amber-400 text-sm underline">Log out</button>
    </form>
</div>
@endsection
