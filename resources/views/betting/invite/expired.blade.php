@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto text-center py-16">
    <h1 class="text-xl text-gray-400">This invitation has expired</h1>
    <a href="{{ route('home') }}" class="mt-6 inline-block text-amber-400 hover:underline">Go home</a>
</div>
@endsection
