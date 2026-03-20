@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">SEO Settings</h1>

<form action="{{ route('admin.seo.update') }}" method="POST" class="max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-sm text-gray-400 mb-1">Site Name</label>
        <input type="text" name="site_name" value="{{ $settings['site_name'] }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Default Meta Title</label>
        <input type="text" name="meta_title_default" value="{{ $settings['meta_title_default'] }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Default Meta Description</label>
        <textarea name="meta_description_default" rows="2" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">{{ $settings['meta_description_default'] }}</textarea>
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Casino Meta Title Pattern</label>
        <input type="text" name="meta_title_pattern" value="{{ $settings['meta_title_pattern'] }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2" placeholder="{Casino Name}, {Year}, {Site Name}">
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Casino Meta Description Pattern</label>
        <textarea name="meta_description_pattern" rows="2" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">{{ $settings['meta_description_pattern'] }}</textarea>
    </div>
    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-6 py-2 rounded-lg">Save</button>
</form>
@endsection
