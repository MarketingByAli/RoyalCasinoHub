@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">Add casino</h1>

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-900/50 bg-red-950/40 px-4 py-3 text-sm text-red-200">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.casinos.store') }}" method="POST" class="max-w-xl space-y-4">
    @csrf
    <div>
        <label class="block text-sm text-gray-400 mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Country</label>
        <input type="text" name="country" value="{{ old('country') }}" required class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Region</label>
            <input type="text" name="region" value="{{ old('region') }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Locality</label>
            <input type="text" name="locality" value="{{ old('locality') }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Website</label>
        <input type="text" name="website" value="{{ old('website') }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Founded (year)</label>
        <input type="text" name="founded" value="{{ old('founded') }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">LinkedIn</label>
        <input type="text" name="linkedin" value="{{ old('linkedin') }}" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Status</label>
        <select name="status" class="w-full bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2">
            <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
    </div>
    <div class="flex gap-3">
        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-6 py-2 rounded-lg">Create</button>
        <a href="{{ route('admin.casinos.index') }}" class="text-gray-400 hover:text-amber-400 px-4 py-2">Cancel</a>
    </div>
</form>
@endsection
