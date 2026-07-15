@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-6">Create event</h1>
<form method="POST" action="{{ route('admin.betting.events.store') }}" class="max-w-xl space-y-4">
    @csrf
    @include('admin.betting.events._form')
    <button type="submit" class="bg-amber-500 text-amber-950 px-4 py-2 rounded-lg font-semibold">Create</button>
</form>
@endsection
