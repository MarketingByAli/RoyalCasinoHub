<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $metaTitle = $metaTitle ?? $meta_title ?? config('app.name');
        $metaDescription = $metaDescription ?? $meta_description ?? 'Trusted online casino reviews and ratings.';
        $canonical = $canonical ?? null;
        $robots = $robots ?? null;
        $schema = $schema ?? null;
        $breadcrumbSchema = $breadcrumbSchema ?? null;
        $noindex = $noindex ?? false;
        $ogImage = $ogImage ?? null;
        $hreflang = $hreflang ?? [];
        $prevPage = $prevPage ?? null;
        $nextPage = $nextPage ?? null;
    @endphp
    <x-seo-head
        :title="$metaTitle"
        :description="$metaDescription"
        :canonical="$canonical"
        :robots="$robots"
        :schema="$schema"
        :breadcrumbSchema="$breadcrumbSchema"
        :noindex="$noindex"
        :image="$ogImage"
        :hreflang="$hreflang"
        :prevPage="$prevPage"
        :nextPage="$nextPage"
    />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,600,700|dm-sans:400,500,600,700" rel="stylesheet" />
</head>
<body class="bg-[#0a0a0f] text-gray-200 min-h-screen font-sans antialiased relative overflow-x-hidden">
    <div class="fixed inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-20%,rgba(212,175,55,0.08),transparent)] pointer-events-none" aria-hidden="true"></div>
    <div class="fixed inset-0 bg-[linear-gradient(to_right,rgba(212,175,55,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(212,175,55,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent)] pointer-events-none" aria-hidden="true"></div>

    <nav class="relative border-b border-amber-900/20 bg-[#0a0a0f]/90 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <span class="text-2xl font-bold text-amber-400 font-serif tracking-tight group-hover:text-amber-300 transition-colors">RoyalCasinoHub</span>
                </a>
                <div class="hidden md:flex items-center gap-6">
                    <form action="{{ route('search') }}" method="GET">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search casinos..."
                            class="bg-slate-900/80 border border-amber-900/20 rounded-lg px-4 py-2 text-sm text-white placeholder-gray-500 focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 focus:outline-none w-52 transition-all">
                    </form>
                    <a href="{{ route('reviews.index') }}" class="text-gray-400 hover:text-amber-400 transition-colors text-sm font-medium">Reviews</a>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="text-amber-400 hover:text-amber-300 text-sm font-medium">Admin</a>
                        @elseif(auth()->user()->role === 'casino_owner')
                            <a href="{{ route('casino-owner.index') }}" class="text-amber-400 hover:text-amber-300 text-sm font-medium">My Listings</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-amber-400 text-sm font-medium transition-colors">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-400 hover:text-amber-400 text-sm font-medium transition-colors">Login</a>
                        <a href="{{ route('register') }}" class="bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-4 py-2 rounded-lg transition-all hover:shadow-lg hover:shadow-amber-500/20">Register</a>
                    @endauth
                </div>

                <button id="mobile-menu-btn" class="md:hidden text-gray-400 hover:text-amber-400 transition-colors p-2" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </nav>

    <div id="mobile-menu" class="md:hidden hidden fixed inset-0 z-40">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" id="mobile-menu-overlay"></div>
        <div class="fixed right-0 top-0 h-full w-72 bg-[#0f0f1a] border-l border-amber-900/20 p-6 overflow-y-auto">
            <div class="flex justify-end mb-6">
                <button id="mobile-menu-close" class="text-gray-400 hover:text-amber-400 p-2" aria-label="Close menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('search') }}" method="GET" class="mb-6">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search casinos..."
                    class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 focus:outline-none transition-all">
            </form>
            <nav class="space-y-2">
                <a href="{{ route('home') }}" class="block px-4 py-2.5 rounded-lg text-gray-300 hover:bg-amber-500/10 hover:text-amber-400 transition-all">Home</a>
                <a href="{{ route('reviews.index') }}" class="block px-4 py-2.5 rounded-lg text-gray-300 hover:bg-amber-500/10 hover:text-amber-400 transition-all">Reviews</a>
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 rounded-lg text-amber-400 hover:bg-amber-500/10 transition-all">Admin</a>
                    @elseif(auth()->user()->role === 'casino_owner')
                        <a href="{{ route('casino-owner.index') }}" class="block px-4 py-2.5 rounded-lg text-amber-400 hover:bg-amber-500/10 transition-all">My Listings</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2.5 rounded-lg text-gray-300 hover:bg-amber-500/10 hover:text-amber-400 transition-all">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-4 py-2.5 rounded-lg text-gray-300 hover:bg-amber-500/10 hover:text-amber-400 transition-all">Login</a>
                    <a href="{{ route('register') }}" class="block px-4 py-2.5 rounded-lg bg-amber-500 text-amber-950 font-semibold text-center hover:bg-amber-400 transition-all">Register</a>
                @endauth
            </nav>
        </div>
    </div>

    <main class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if(session('success'))
            <div class="mb-6 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="relative border-t border-amber-900/20 mt-20 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} RoyalCasinoHub. All rights reserved.</p>
                <p class="text-gray-600 text-sm">Responsible gambling. 18+ only.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded',function(){
        const btn=document.getElementById('mobile-menu-btn'),menu=document.getElementById('mobile-menu'),
              close=document.getElementById('mobile-menu-close'),overlay=document.getElementById('mobile-menu-overlay');
        function toggle(){menu.classList.toggle('hidden')}
        if(btn){btn.addEventListener('click',toggle)}
        if(close){close.addEventListener('click',toggle)}
        if(overlay){overlay.addEventListener('click',toggle)}
    });
    </script>
</body>
</html>
