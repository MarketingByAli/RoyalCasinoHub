@extends('layouts.app')

@section('content')
<div class="max-w-4xl">
    <nav class="mb-6 text-sm text-gray-500">
        <a href="{{ route('casino-owner.index') }}" class="hover:text-amber-400">My listings</a>
        <span class="text-gray-600"> / </span>
        <span class="text-amber-400">Analytics</span>
    </nav>
    <h1 class="text-2xl font-bold text-amber-400 font-serif mb-2">Traffic — {{ $casino->name }}</h1>
    <p class="text-gray-500 text-sm mb-8">Daily page views (last 30 days, tracked server-side).</p>

    @if($series->isEmpty())
        <p class="text-gray-500">No view data yet. Traffic accumulates as visitors open your public listing.</p>
    @else
        <div class="overflow-x-auto border border-amber-900/20 rounded-xl">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-amber-900/30 text-gray-400">
                        <th class="p-3">Day</th>
                        <th class="p-3">Views</th>
                    </tr>
                </thead>
                <tbody class="text-gray-300">
                    @foreach($series as $row)
                        <tr class="border-b border-amber-900/10">
                            <td class="p-3">{{ $row->day->format('M j, Y') }}</td>
                            <td class="p-3">{{ $row->views }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
