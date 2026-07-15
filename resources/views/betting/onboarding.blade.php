@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-amber-400 mb-6">Complete betting profile</h1>
    <form method="POST" action="{{ route('betting.onboarding.store') }}" class="space-y-4 bg-slate-900/50 border border-amber-900/25 rounded-xl p-6">
        @csrf
        @include('betting.partials.profile-fields', ['minimumAge' => config('betting.minimum_age', 18)])
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold py-3 rounded-xl">Continue</button>
    </form>
</div>
@endsection
