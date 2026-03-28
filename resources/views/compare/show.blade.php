@extends('layouts.app')

@section('content')
<nav class="mb-6 text-sm" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Home</a></li>
        <li class="text-gray-600">/</li>
        <li class="text-amber-400">Compare</li>
    </ol>
</nav>

<h1 class="text-3xl font-bold text-white font-serif mb-2">Compare casinos</h1>
<p class="text-gray-500 mb-8">Add up to three casinos via URL: <code class="text-amber-400/80 text-sm">/compare?casinos=slug-one,slug-two</code></p>

@if($casinos->isEmpty())
    <p class="text-gray-500">No casinos selected. Open a casino page and use “Add to compare” or pass slugs in the query string.</p>
@else
    <div class="overflow-x-auto border border-amber-900/20 rounded-2xl">
        <table class="min-w-full text-sm text-left">
            <thead>
                <tr class="border-b border-amber-900/30 text-gray-400">
                    <th class="p-4 font-medium">Attribute</th>
                    @foreach($casinos as $c)
                        <th class="p-4 font-semibold text-amber-400 min-w-[10rem]">
                            <a href="{{ route('casino.show', $c->slug) }}" class="hover:underline">{{ $c->name }}</a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="text-gray-300">
                <tr class="border-b border-amber-900/10">
                    <td class="p-4 text-gray-500">Rating</td>
                    @foreach($casinos as $c)
                        <td class="p-4">{{ $c->average_rating ? number_format((float) $c->average_rating, 1).' / 5' : '—' }}</td>
                    @endforeach
                </tr>
                <tr class="border-b border-amber-900/10">
                    <td class="p-4 text-gray-500">Reviews</td>
                    @foreach($casinos as $c)
                        <td class="p-4">{{ $c->reviews_count ?? 0 }}</td>
                    @endforeach
                </tr>
                <tr class="border-b border-amber-900/10">
                    <td class="p-4 text-gray-500">Country</td>
                    @foreach($casinos as $c)
                        <td class="p-4">{{ $c->country }}</td>
                    @endforeach
                </tr>
                <tr class="border-b border-amber-900/10">
                    <td class="p-4 text-gray-500">Min deposit</td>
                    @foreach($casinos as $c)
                        <td class="p-4">{{ $c->min_deposit !== null ? $c->min_deposit : '—' }}</td>
                    @endforeach
                </tr>
                <tr class="border-b border-amber-900/10">
                    <td class="p-4 text-gray-500">Withdrawal</td>
                    @foreach($casinos as $c)
                        <td class="p-4">{{ $c->withdrawal_time_text ?? '—' }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td class="p-4 text-gray-500">License</td>
                    @foreach($casinos as $c)
                        <td class="p-4">{{ $c->license ?? '—' }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
@endif
@endsection
