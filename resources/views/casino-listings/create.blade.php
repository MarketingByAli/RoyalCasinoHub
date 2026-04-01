@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-2">Submit a casino listing</h1>
    <p class="text-gray-500 text-sm mb-6">Your submission will stay <strong class="text-gray-400">pending</strong> until listing payment (coming soon) and admin review. You must verify your email first.</p>

    <form method="POST" action="{{ route('casino-listings.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-gray-400 mb-1">Casino name <span class="text-red-400">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('name') border-red-500 @enderror">
            @error('name')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Country <span class="text-red-400">*</span></label>
            <input type="text" name="country" value="{{ old('country') }}" required placeholder="e.g. United Kingdom"
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('country') border-red-500 @enderror">
            @error('country')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Region / state</label>
                <input type="text" name="region" value="{{ old('region') }}"
                    class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('region') border-red-500 @enderror">
                @error('region')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">City / locality</label>
                <input type="text" name="locality" value="{{ old('locality') }}"
                    class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('locality') border-red-500 @enderror">
                @error('locality')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Website</label>
            <input type="text" name="website" value="{{ old('website') }}" placeholder="example.com or https://..."
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('website') border-red-500 @enderror">
            @error('website')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Founded (year)</label>
            <input type="text" name="founded" value="{{ old('founded') }}" placeholder="e.g. 2021" inputmode="numeric"
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('founded') border-red-500 @enderror">
            @error('founded')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">LinkedIn</label>
            <input type="text" name="linkedin" value="{{ old('linkedin') }}" placeholder="Company page URL"
                class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('linkedin') border-red-500 @enderror">
            @error('linkedin')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold py-2.5 rounded-lg">Submit listing</button>
    </form>
</div>
@endsection
