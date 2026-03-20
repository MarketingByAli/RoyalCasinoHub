<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-seo-head
        title="Admin | RoyalCasinoHub"
        description="RoyalCasinoHub admin panel for managing casinos, reviews, and listings."
        :canonical="url()->current()"
        robots="noindex,nofollow"
        :noindex="true"
    />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet" />
</head>
<body class="bg-[#0f0f1a] text-gray-200 min-h-screen">
    <div class="flex">
        <aside class="w-64 min-h-screen bg-slate-900/50 border-r border-amber-900/30 p-4">
            <a href="{{ route('admin.dashboard') }}" class="block text-xl font-bold text-amber-400 mb-8">Admin</a>
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg hover:bg-amber-500/20 {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400' }}">Dashboard</a>
                <a href="{{ route('admin.casinos.index') }}" class="block px-4 py-2 rounded-lg hover:bg-amber-500/20 {{ request()->routeIs('admin.casinos.*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400' }}">Casinos</a>
                <a href="{{ route('admin.import.index') }}" class="block px-4 py-2 rounded-lg hover:bg-amber-500/20 {{ request()->routeIs('admin.import.*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400' }}">CSV Import</a>
                <a href="{{ route('admin.reviews.index') }}" class="block px-4 py-2 rounded-lg hover:bg-amber-500/20 {{ request()->routeIs('admin.reviews.*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400' }}">Reviews</a>
                <a href="{{ route('admin.claims.index') }}" class="block px-4 py-2 rounded-lg hover:bg-amber-500/20 {{ request()->routeIs('admin.claims.*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400' }}">Claims</a>
                <a href="{{ route('admin.redirects.index') }}" class="block px-4 py-2 rounded-lg hover:bg-amber-500/20 {{ request()->routeIs('admin.redirects.*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400' }}">Redirects</a>
                <a href="{{ route('admin.seo.index') }}" class="block px-4 py-2 rounded-lg hover:bg-amber-500/20 {{ request()->routeIs('admin.seo.*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400' }}">SEO Settings</a>
                <a href="{{ route('admin.enrichment.index') }}" class="block px-4 py-2 rounded-lg hover:bg-amber-500/20 {{ request()->routeIs('admin.enrichment.*') ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400' }}">Enrichment</a>
            </nav>
            <a href="{{ route('home') }}" class="block mt-8 px-4 py-2 text-gray-500 hover:text-amber-400">← Back to Site</a>
        </aside>
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="mb-6 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
