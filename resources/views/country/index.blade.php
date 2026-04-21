@extends('layouts.app')

@section('content')
<nav style="margin-bottom:24px;font-size:14px" aria-label="Breadcrumb">
    <ol style="display:flex;flex-wrap:wrap;gap:8px;color:#6b7280;list-style:none;padding:0;margin:0">
        <li><a href="{{ route('home') }}" style="color:#6b7280;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='#6b7280'">Home</a></li>
        <li style="color:#4b5563">/</li>
        <li style="color:#fbbf24">Countries</li>
    </ol>
</nav>

<div style="margin-bottom:48px">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
        <div style="width:48px;height:48px;border-radius:14px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg style="width:24px;height:24px;color:#fbbf24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h1 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;font-family:'Playfair Display',serif;color:#fff;margin:0">Browse by Country</h1>
            <p style="color:#6b7280;font-size:14px;margin-top:4px">Explore <span style="color:#fbbf24;font-weight:600">{{ $countries->count() }}</span> countries with verified online casinos</p>
        </div>
    </div>
</div>

@php
    $grouped = $countries->groupBy(function($c) {
        return mb_strtoupper(mb_substr($c->country, 0, 1));
    })->sortKeys();
@endphp

@foreach($grouped as $letter => $group)
<div style="margin-bottom:40px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
        <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,rgba(245,158,11,0.15),rgba(180,120,30,0.05));border:1px solid rgba(245,158,11,0.25);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fbbf24;font-family:'Playfair Display',serif;flex-shrink:0">
            {{ $letter }}
        </div>
        <div style="flex:1;height:1px;background:linear-gradient(to right,rgba(180,120,30,0.2),transparent)"></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px">
        @foreach($group as $c)
        <a href="{{ route('country.show', $c->country_slug) }}"
           style="display:flex;align-items:center;gap:14px;background:rgba(15,15,25,0.6);border:1px solid rgba(180,120,30,0.12);padding:16px 18px;border-radius:14px;text-decoration:none;transition:all 0.3s;overflow:hidden"
           onmouseover="this.style.background='rgba(245,158,11,0.06)';this.style.borderColor='rgba(245,158,11,0.35)';this.style.boxShadow='0 4px 20px rgba(0,0,0,0.15)'"
           onmouseout="this.style.background='rgba(15,15,25,0.6)';this.style.borderColor='rgba(180,120,30,0.12)';this.style.boxShadow='none'">
            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,rgba(245,158,11,0.08),rgba(180,120,30,0.04));border:1px solid rgba(180,120,30,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg style="width:18px;height:18px;color:rgba(217,119,6,0.5)" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div style="min-width:0;flex:1">
                <div style="font-size:14px;font-weight:600;color:#e5e7eb;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;transition:color 0.2s">{{ $c->country }}</div>
                <div style="font-size:12px;color:#4b5563;margin-top:2px">{{ $c->casinos_count }} {{ Str::plural('casino', $c->casinos_count) }}</div>
            </div>
            <svg style="width:16px;height:16px;color:rgba(180,120,30,0.4);flex-shrink:0;transition:color 0.2s" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endforeach
    </div>
</div>
@endforeach

@if($countries->isEmpty())
<div style="text-align:center;padding:80px 0">
    <div style="width:64px;height:64px;background:rgba(15,15,25,0.8);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;border:1px solid rgba(180,120,30,0.2)">
        <svg style="width:28px;height:28px;color:rgba(180,120,30,0.4)" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <p style="color:#4b5563;font-size:14px">No countries found yet.</p>
</div>
@endif
@endsection
