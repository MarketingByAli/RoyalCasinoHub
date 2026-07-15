@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-start gap-4 mb-8">
        @if($profile->avatar_path)
            <img src="{{ Storage::url($profile->avatar_path) }}" alt="" class="w-16 h-16 rounded-full object-cover">
        @else
            <div class="w-16 h-16 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400 text-xl font-bold">{{ strtoupper(substr($profile->username, 0, 1)) }}</div>
        @endif
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $profile->display_name ?? $profile->username }}</h1>
            <p class="text-gray-500">{{ '@'.$profile->username }}</p>
            @if($profile->bio)<p class="text-gray-400 mt-2 text-sm">{{ $profile->bio }}</p>@endif
        </div>
    </div>

    @if($stats)
        <div class="p-5 rounded-xl border border-amber-900/25 bg-slate-900/50 mb-6">
            <h2 class="font-semibold text-amber-400 mb-3">Betting record</h2>
            <p class="text-xs text-gray-500 mb-4">Win rate alone can mislead — net points matter more.</p>
            <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div><dt class="text-gray-500">Completed</dt><dd class="text-lg text-white">{{ $stats['completed_bets'] }}</dd></div>
                <div><dt class="text-gray-500">Wins / Losses</dt><dd class="text-lg text-white">{{ $stats['wins'] }} / {{ $stats['losses'] }}</dd></div>
                <div><dt class="text-gray-500">Net points</dt><dd class="text-lg {{ $stats['net_points'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ $stats['net_points'] >= 0 ? '+' : '' }}{{ number_format($stats['net_points'], 0) }}</dd></div>
                <div><dt class="text-gray-500">Voided</dt><dd>{{ $stats['voided_bets'] }}</dd></div>
                <div><dt class="text-gray-500">Disputes filed</dt><dd>{{ $stats['dispute_count'] }}</dd></div>
                <div><dt class="text-gray-500">Dispute rate</dt><dd>{{ number_format($stats['dispute_rate'] * 100, 1) }}%</dd></div>
                <div><dt class="text-gray-500">Account age</dt><dd>{{ $stats['account_age_days'] }} days</dd></div>
            </dl>
        </div>
    @else
        <p class="text-gray-500 mb-6">Betting activity is hidden.</p>
    @endif

    @auth
        @if(auth()->id() !== $user->id)
            <div class="flex flex-wrap gap-3">
                @if($isFollowing)
                    <form action="{{ route('betting.users.unfollow', $user) }}" method="POST">@csrf @method('DELETE')<button class="text-sm text-gray-400 hover:text-white">Unfollow</button></form>
                @else
                    <form action="{{ route('betting.users.follow', $user) }}" method="POST">@csrf<button class="text-sm text-amber-400 hover:underline">Follow</button></form>
                @endif
                <form action="{{ route('betting.users.block', $user) }}" method="POST">@csrf<button class="text-sm text-red-400/80 hover:underline">Block</button></form>
                <details class="text-sm">
                    <summary class="text-gray-400 cursor-pointer hover:text-white">Report user</summary>
                    <form action="{{ route('betting.users.report', $user) }}" method="POST" class="mt-2 space-y-2">
                        @csrf
                        <select name="reason" required class="w-full bg-slate-950 border border-amber-900/30 rounded px-2 py-1 text-white text-sm">
                            <option value="harassment">Harassment</option>
                            <option value="spam">Spam</option>
                            <option value="fraud">Fraud</option>
                            <option value="other">Other</option>
                        </select>
                        <textarea name="explanation" rows="2" placeholder="Details..." class="w-full bg-slate-950 border border-amber-900/30 rounded px-2 py-1 text-white text-sm"></textarea>
                        <button type="submit" class="text-amber-400 hover:underline">Submit report</button>
                    </form>
                </details>
            </div>
        @elseif(auth()->user()->bettingProfile)
            <a href="{{ route('betting.profiles.edit') }}" class="text-amber-400 text-sm hover:underline">Edit profile</a>
        @endif
    @endauth
</div>
@endsection
