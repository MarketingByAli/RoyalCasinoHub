@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 mb-6">Edit profile</h1>
    <form method="POST" action="{{ route('betting.profiles.update') }}" enctype="multipart/form-data" class="space-y-4 bg-slate-900/50 border border-amber-900/25 rounded-xl p-6">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm text-gray-300 mb-1">Display name</label>
            <input type="text" name="display_name" value="{{ old('display_name', $profile->display_name) }}" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Bio</label>
            <textarea name="bio" rows="3" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">{{ old('bio', $profile->bio) }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-300 mb-1">Avatar</label>
            <input type="file" name="avatar" accept="image/*" class="text-sm text-gray-400">
        </div>
        <label class="flex gap-2 text-sm text-gray-400"><input type="checkbox" name="hide_wager_amounts" value="1" @checked($profile->hide_wager_amounts)> Hide wager amounts on profile</label>
        <label class="flex gap-2 text-sm text-gray-400"><input type="checkbox" name="hide_betting_activity" value="1" @checked($profile->hide_betting_activity)> Hide betting activity</label>
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold py-3 rounded-xl">Save</button>
    </form>
</div>
@endsection
