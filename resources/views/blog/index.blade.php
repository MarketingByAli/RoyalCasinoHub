@extends('layouts.app')

@section('content')
<nav class="mb-6 text-sm" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Home</a></li>
        <li class="text-gray-600">/</li>
        <li class="text-amber-400">Blog</li>
    </ol>
</nav>

<h1 class="text-3xl md:text-4xl font-bold text-white font-serif mb-10">Blog</h1>

<div class="space-y-6">
    @forelse($posts as $post)
        <article class="bg-slate-900/60 border border-amber-900/20 rounded-xl p-6 hover:border-amber-900/30 transition-colors">
            <h2 class="text-xl font-semibold text-white">
                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-amber-400 transition-colors">{{ $post->title }}</a>
            </h2>
            @if($post->excerpt)
                <p class="text-gray-400 mt-2">{{ $post->excerpt }}</p>
            @endif
            <p class="text-xs text-gray-600 mt-3">{{ $post->published_at?->format('M j, Y') }}</p>
        </article>
    @empty
        <p class="text-gray-500">No posts published yet.</p>
    @endforelse
</div>

<div class="mt-10">{{ $posts->links() }}</div>
@endsection
