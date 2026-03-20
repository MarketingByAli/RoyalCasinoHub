@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <p class="text-sm text-gray-400">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium text-amber-400/90">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium text-amber-400/90">{{ $paginator->lastItem() }}</span>
            @else
                <span class="font-medium text-amber-400/90">{{ $paginator->count() }}</span>
            @endif
            {!! __('of') !!}
            <span class="font-medium text-amber-400/90">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>

        <div class="flex gap-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-slate-800/50 border border-amber-900/30 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-slate-800/50 border border-amber-900/30 rounded-lg hover:border-amber-500/50 hover:text-amber-400 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </a>
            @endif

            <span class="inline-flex gap-1 flex-wrap">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex items-center px-3 py-2 text-sm text-gray-500">...</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-amber-950 bg-amber-400 border border-amber-400 rounded-lg">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-slate-800/50 border border-amber-900/30 rounded-lg hover:border-amber-500/50 hover:text-amber-400 transition" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-slate-800/50 border border-amber-900/30 rounded-lg hover:border-amber-500/50 hover:text-amber-400 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </a>
            @else
                <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-slate-800/50 border border-amber-900/30 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
