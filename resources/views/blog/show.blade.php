@extends('layouts.app')

@section('content')
<nav class="mb-6 text-sm" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Home</a></li>
        <li class="text-gray-600">/</li>
        <li><a href="{{ route('blog.index') }}" class="hover:text-amber-400 transition-colors">Blog</a></li>
        <li class="text-gray-600">/</li>
        <li class="text-amber-400">{{ Str::limit($post->title, 40) }}</li>
    </ol>
</nav>

<article class="prose prose-invert prose-amber max-w-none">
    <h1 class="text-3xl md:text-4xl font-bold text-white font-serif mb-4">{{ $post->title }}</h1>
    <p class="text-sm text-gray-500 mb-8">{{ $post->published_at?->format('F j, Y') }}</p>
    <div class="text-gray-300 leading-relaxed">{!! $post->body !!}</div>
</article>
@endsection
