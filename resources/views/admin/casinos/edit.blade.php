@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">Edit Casino: {{ $casino->name }}</h1>

<form action="{{ route('admin.casinos.update', $casino) }}" method="POST" class="max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $casino->name) }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $casino->slug) }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Country</label>
            <input type="text" name="country" value="{{ old('country', $casino->country) }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Country Slug</label>
            <input type="text" name="country_slug" value="{{ old('country_slug', $casino->country_slug) }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Website</label>
        <input type="url" name="website" value="{{ old('website', $casino->website) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Logo URL</label>
            <input type="url" name="logo_url" value="{{ old('logo_url', $casino->logo_url) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Logo Alt</label>
            <input type="text" name="logo_alt" value="{{ old('logo_alt', $casino->logo_alt) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Screenshot URL</label>
            <input type="url" name="screenshot_url" value="{{ old('screenshot_url', $casino->screenshot_url) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Screenshot Alt</label>
            <input type="text" name="screenshot_alt" value="{{ old('screenshot_alt', $casino->screenshot_alt) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Description</label>
        <textarea name="description" rows="6" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">{{ old('description', $casino->description) }}</textarea>
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Short Description</label>
        <input type="text" name="short_description" value="{{ old('short_description', $casino->short_description) }}" maxlength="500" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Status</label>
        <select name="status" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            <option value="published" {{ $casino->status === 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ $casino->status === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="pending" {{ $casino->status === 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Meta Title</label>
            <input type="text" name="meta_title" value="{{ old('meta_title', $casino->meta_title) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Robots</label>
            <input type="text" name="robots" value="{{ old('robots', $casino->robots) }}" placeholder="index,follow" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Meta Description</label>
        <textarea name="meta_description" rows="2" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">{{ old('meta_description', $casino->meta_description) }}</textarea>
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Canonical URL</label>
        <input type="url" name="canonical_url" value="{{ old('canonical_url', $casino->canonical_url) }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-6 py-2 rounded-lg">Save</button>
</form>
@endsection
