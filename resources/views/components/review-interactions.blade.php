@props(['review', 'casino', 'userVote' => null])

@php
    $voted = $userVote === null ? null : (bool) $userVote;
@endphp

<div {{ $attributes->merge(['class' => 'mt-4 space-y-3 border-t border-amber-900/10 pt-4']) }}>
    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
        <span><span class="text-amber-400/90 font-medium">{{ $review->helpful_up_count }}</span> found this helpful</span>
        @if($review->helpful_down_count > 0)
            <span>· <span class="text-gray-400">{{ $review->helpful_down_count }}</span> not helpful</span>
        @endif
    </div>

    @auth
        @can('vote', $review)
            <div class="flex flex-wrap gap-2 items-center">
                <form action="{{ route('reviews.vote', $review) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="helpful" value="1">
                    <button type="submit" class="text-sm px-3 py-1.5 rounded-lg border transition {{ $voted === true ? 'border-amber-500 bg-amber-500/15 text-amber-300' : 'border-amber-900/30 text-gray-400 hover:border-amber-500/40' }}">Helpful</button>
                </form>
                <form action="{{ route('reviews.vote', $review) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="helpful" value="0">
                    <button type="submit" class="text-sm px-3 py-1.5 rounded-lg border transition {{ $voted === false ? 'border-amber-500 bg-amber-500/15 text-amber-300' : 'border-amber-900/30 text-gray-400 hover:border-amber-500/40' }}">Not helpful</button>
                </form>
            </div>
        @endcan

        @can('report', $review)
            <details class="text-sm">
                <summary class="cursor-pointer text-gray-500 hover:text-amber-400/80">Report this review</summary>
                <form action="{{ route('reviews.report', $review) }}" method="POST" class="mt-3 space-y-2 max-w-md">
                    @csrf
                    <select name="reason" required class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-3 py-2 text-white text-sm">
                        <option value="spam">Spam</option>
                        <option value="inappropriate">Inappropriate</option>
                        <option value="misleading">Misleading</option>
                        <option value="other">Other</option>
                    </select>
                    <textarea name="details" rows="2" maxlength="2000" placeholder="Optional details" class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-3 py-2 text-white text-sm placeholder-gray-600"></textarea>
                    <button type="submit" class="text-sm bg-amber-900/40 hover:bg-amber-900/60 text-amber-200 px-3 py-1.5 rounded-lg">Submit report</button>
                </form>
            </details>
        @endcan

        @can('reply', $review)
            <details class="text-sm">
                <summary class="cursor-pointer text-amber-500/90 hover:text-amber-400">Reply as {{ $casino->name }}</summary>
                <form action="{{ route('reviews.replies.store', $review) }}" method="POST" class="mt-3 space-y-2">
                    @csrf
                    <textarea name="body" rows="3" required maxlength="5000" placeholder="Official response to this review…" class="w-full bg-slate-900/80 border border-amber-900/20 rounded-lg px-3 py-2 text-white text-sm placeholder-gray-600">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-red-400 text-xs">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="text-sm bg-amber-500 hover:bg-amber-400 text-amber-950 font-medium px-3 py-1.5 rounded-lg">Post reply</button>
                </form>
            </details>
        @endcan
    @endauth

    @if($review->ownerReply)
        <div class="mt-3 pl-4 border-l-2 border-amber-600/50 bg-slate-950/40 rounded-r-lg py-3 pr-3">
            <p class="text-xs text-amber-500/90 font-semibold uppercase tracking-wide">Official response</p>
            <p class="text-gray-300 mt-1 text-sm whitespace-pre-wrap">{{ $review->ownerReply->body }}</p>
            <p class="text-xs text-gray-600 mt-2">Posted {{ $review->ownerReply->created_at->diffForHumans() }}</p>
        </div>
    @endif
</div>
