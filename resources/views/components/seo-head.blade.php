@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => null,
    'schema' => null,
    'breadcrumbSchema' => null,
    'noindex' => false,
    'image' => null,
    'type' => 'website',
    'hreflang' => [],
    'prevPage' => null,
    'nextPage' => null,
])

@php
    $siteName = \App\Models\SeoSetting::get('site_name', 'RoyalCasinoHub');
    $siteUrl = config('app.url');
    $finalTitle = $title ?? $siteName;
    $finalDescription = $description ?? 'Trusted online casino reviews, ratings, and bonuses.';
    $finalCanonical = $canonical ?? url()->current();
    $finalRobots = $noindex ? 'noindex,nofollow' : ($robots ?? 'index,follow');
    $ogImage = $image ?? 'https://via.placeholder.com/1200x630/0f0f1a/D4AF37?text=' . urlencode($siteName);
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $finalTitle }}</title>
<meta name="description" content="{{ Str::limit($finalDescription, 160) }}">
<link rel="canonical" href="{{ $finalCanonical }}">
<meta name="robots" content="{{ $finalRobots }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $finalCanonical }}">
<meta property="og:title" content="{{ $finalTitle }}">
<meta property="og:description" content="{{ Str::limit($finalDescription, 160) }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $finalCanonical }}">
<meta name="twitter:title" content="{{ $finalTitle }}">
<meta name="twitter:description" content="{{ Str::limit($finalDescription, 160) }}">
<meta name="twitter:image" content="{{ $ogImage }}">

@if($prevPage)
<link rel="prev" href="{{ $prevPage }}">
@endif
@if($nextPage)
<link rel="next" href="{{ $nextPage }}">
@endif

@foreach($hreflang as $lang => $url)
<link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
@endforeach
@if(!empty($hreflang) && !isset($hreflang['x-default']))
<link rel="alternate" hreflang="x-default" href="{{ $finalCanonical }}">
@endif

@if($schema)
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endif

@if($breadcrumbSchema)
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endif
