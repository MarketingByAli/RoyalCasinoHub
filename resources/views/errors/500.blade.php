<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Server Error | RoyalCasinoHub</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:700|dm-sans:400,600" rel="stylesheet" />
</head>
<body class="bg-[#0a0a0f] text-gray-200 min-h-screen font-sans antialiased flex items-center justify-center">
    <div class="text-center px-6">
        <p class="text-8xl font-bold text-amber-400 font-serif mb-4">500</p>
        <h1 class="text-2xl font-semibold text-white mb-3">Server Error</h1>
        <p class="text-gray-400 mb-8 max-w-md mx-auto">Something went wrong on our end. Please try again later.</p>
        <a href="{{ url('/') }}" class="inline-block bg-amber-500 hover:bg-amber-400 text-amber-950 font-semibold px-8 py-3 rounded-xl transition-all hover:shadow-lg hover:shadow-amber-500/25">Back to Home</a>
    </div>
</body>
</html>
