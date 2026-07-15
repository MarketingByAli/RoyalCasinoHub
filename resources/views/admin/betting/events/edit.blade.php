@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-6">Edit event</h1>
<form method="POST" action="{{ route('admin.betting.events.update', $event) }}" class="max-w-xl space-y-4">
    @csrf @method('PUT')
    @include('admin.betting.events._form', ['event' => $event])
    <button type="submit" class="bg-amber-500 text-amber-950 px-4 py-2 rounded-lg font-semibold">Save</button>
</form>
<form method="POST" action="{{ route('admin.betting.events.publish-result', $event) }}" class="max-w-xl mt-8 p-4 border border-amber-900/30 rounded-lg space-y-3">
    @csrf
    <h2 class="font-semibold text-white">Publish result (all matched markets)</h2>
    <input type="text" name="winning_outcome" placeholder="Winning outcome" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
    <button type="submit" class="text-amber-400 text-sm hover:underline">Publish result</button>
</form>
@endsection
