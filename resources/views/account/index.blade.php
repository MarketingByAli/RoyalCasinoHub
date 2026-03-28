@extends('layouts.app')

@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-2">My account</h1>
    <p class="text-gray-500 text-sm mb-8">Manage your profile and see your activity.</p>
    <div class="grid sm:grid-cols-2 gap-4">
        <a href="{{ route('account.favorites') }}" class="block bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 hover:border-amber-500/30 transition-colors">
            <h2 class="font-semibold text-white mb-1">Saved casinos</h2>
            <p class="text-sm text-gray-500">Your favorite listings</p>
        </a>
        <a href="{{ route('account.profile.edit') }}" class="block bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 hover:border-amber-500/30 transition-colors">
            <h2 class="font-semibold text-white mb-1">Profile</h2>
            <p class="text-sm text-gray-500">Update your name and password</p>
        </a>
        <a href="{{ route('account.reviews') }}" class="block bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 hover:border-amber-500/30 transition-colors">
            <h2 class="font-semibold text-white mb-1">My reviews</h2>
            <p class="text-sm text-gray-500">Pending and published reviews</p>
        </a>
        <a href="{{ route('account.claims') }}" class="block bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 hover:border-amber-500/30 transition-colors">
            <h2 class="font-semibold text-white mb-1">My claims</h2>
            <p class="text-sm text-gray-500">Listing claim status</p>
        </a>
        @if(auth()->user()->role === 'casino_owner')
            <a href="{{ route('casino-owner.index') }}" class="block bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 hover:border-amber-500/30 transition-colors">
                <h2 class="font-semibold text-white mb-1">My listings</h2>
                <p class="text-sm text-gray-500">Edit claimed casinos</p>
            </a>
        @endif
    </div>

    <div class="mt-10 bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 max-w-xl">
        <h2 class="font-semibold text-white mb-4">Email preferences</h2>
        <form action="{{ route('account.settings') }}" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="hidden" name="digest_weekly" value="0">
                <input type="checkbox" name="digest_weekly" value="1" class="rounded border-amber-900/40" {{ old('digest_weekly', auth()->user()->settings['digest_weekly'] ?? false) ? 'checked' : '' }}>
                Weekly digest (saved casinos activity)
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="hidden" name="marketing_emails" value="0">
                <input type="checkbox" name="marketing_emails" value="1" class="rounded border-amber-900/40" {{ old('marketing_emails', auth()->user()->settings['marketing_emails'] ?? false) ? 'checked' : '' }}>
                Product updates &amp; tips
            </label>
            <button type="submit" class="text-sm bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 px-4 py-2 rounded-lg">Save preferences</button>
        </form>
    </div>

    <div class="mt-10 border border-red-900/30 rounded-xl p-6 max-w-xl">
        <h2 class="font-semibold text-red-400 mb-2">Delete account</h2>
        <p class="text-sm text-gray-500 mb-4">Your reviews will be anonymized. This cannot be undone.</p>
        <form action="{{ route('account.destroy') }}" method="POST" onsubmit="return confirm('Delete your account permanently?');">
            @csrf
            @method('DELETE')
            <input type="password" name="password" required placeholder="Current password" class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-3 py-2 text-white text-sm mb-3">
            <button type="submit" class="text-sm bg-red-500/20 hover:bg-red-500/30 text-red-300 px-4 py-2 rounded-lg">Delete my account</button>
        </form>
    </div>
</div>
@endsection
