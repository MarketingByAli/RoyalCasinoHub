@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 font-serif mb-8">Edit: {{ $casino->name }}</h1>

<form action="{{ route('casino-owner.update', $casino) }}" method="POST" class="max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-sm text-gray-400 mb-1">Description</label>
        <textarea name="description" rows="8" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('description') border-red-500 @enderror">{{ old('description', $casino->description) }}</textarea>
        @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Short Description</label>
        <input type="text" name="short_description" value="{{ old('short_description', $casino->short_description) }}" maxlength="500" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('short_description') border-red-500 @enderror">
        @error('short_description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Logo URL</label>
            <input type="url" name="logo_url" value="{{ old('logo_url', $casino->logo_url) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('logo_url') border-red-500 @enderror">
            @error('logo_url') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Logo Alt Text</label>
            <input type="text" name="logo_alt" value="{{ old('logo_alt', $casino->logo_alt) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('logo_alt') border-red-500 @enderror">
            @error('logo_alt') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Screenshot URL</label>
            <input type="url" name="screenshot_url" value="{{ old('screenshot_url', $casino->screenshot_url) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('screenshot_url') border-red-500 @enderror">
            @error('screenshot_url') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Screenshot Alt Text</label>
            <input type="text" name="screenshot_alt" value="{{ old('screenshot_alt', $casino->screenshot_alt) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('screenshot_alt') border-red-500 @enderror">
            @error('screenshot_alt') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Contact Email</label>
            <input type="email" name="email" value="{{ old('email', $casino->email) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('email') border-red-500 @enderror">
            @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $casino->phone) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white @error('phone') border-red-500 @enderror">
            @error('phone') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-6 py-2 rounded-lg">Save Changes</button>
</form>
@endsection
