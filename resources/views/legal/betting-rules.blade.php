@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto prose prose-invert prose-amber">
    <h1 class="text-3xl font-bold text-amber-400 font-serif not-prose">Betting rules (play money)</h1>
    <p class="text-gray-400 not-prose">Stage 0 — challenges use play points only. No cash value, no withdrawals.</p>
    <ul class="text-gray-300 space-y-2 mt-6 not-prose list-disc pl-6">
        <li>Challenges must use admin-approved events with a published settlement source.</li>
        <li>Terms become immutable once a challenger accepts and stakes are locked.</li>
        <li>Prohibited markets (personal harm, minors, harassment, etc.) are rejected or manually reviewed.</li>
        <li>Results are published by the platform; creators never settle outcomes.</li>
        <li>A dispute window applies after results; open disputes pause settlement.</li>
        <li>Maximum stake and exposure limits apply per user.</li>
        <li>Real-money betting requires licensing and is not available in Stage 0.</li>
    </ul>
</div>
@endsection
