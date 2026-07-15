<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\Follower;
use App\Betting\Models\UserBlock;
use App\Betting\Models\UserProfile;
use App\Betting\Models\UserReport;
use App\Betting\Services\BettingStatsService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(string $username, BettingStatsService $statsService)
    {
        $profile = UserProfile::where('username', $username)->with('user')->firstOrFail();
        $user = $profile->user;

        if (auth()->check() && auth()->user()->isBlockedBy($user)) {
            abort(404);
        }

        $stats = $profile->hide_betting_activity ? null : $statsService->forUser($user);
        $isFollowing = auth()->check()
            ? Follower::where('follower_id', auth()->id())->where('following_id', $user->id)->exists()
            : false;

        return view('betting.profiles.show', compact('profile', 'user', 'stats', 'isFollowing'));
    }

    public function edit()
    {
        $profile = auth()->user()->bettingProfile;

        return view('betting.profiles.edit', compact('profile'));
    }

    public function update(Request $request)
    {
        $profile = auth()->user()->bettingProfile;

        $validated = $request->validate([
            'display_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'hide_wager_amounts' => 'boolean',
            'hide_betting_activity' => 'boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($profile->avatar_path) {
                Storage::disk('public')->delete($profile->avatar_path);
            }
            $validated['avatar_path'] = $request->file('avatar')->store('betting-avatars', 'public');
        }

        $profile->update([
            'display_name' => $validated['display_name'] ?? $profile->display_name,
            'bio' => $validated['bio'] ?? null,
            'hide_wager_amounts' => $request->boolean('hide_wager_amounts'),
            'hide_betting_activity' => $request->boolean('hide_betting_activity'),
            'avatar_path' => $validated['avatar_path'] ?? $profile->avatar_path,
        ]);

        return redirect()->route('betting.profiles.show', $profile->username)->with('success', 'Profile updated.');
    }

    public function follow(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        Follower::firstOrCreate([
            'follower_id' => auth()->id(),
            'following_id' => $user->id,
        ]);

        return back()->with('success', 'Following '.$user->bettingProfile?->username);
    }

    public function unfollow(User $user)
    {
        Follower::where('follower_id', auth()->id())->where('following_id', $user->id)->delete();

        return back()->with('success', 'Unfollowed.');
    }

    public function block(User $user)
    {
        UserBlock::firstOrCreate([
            'blocker_id' => auth()->id(),
            'blocked_id' => $user->id,
        ]);

        return back()->with('success', 'User blocked.');
    }

    public function report(Request $request, User $user)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:64',
            'explanation' => 'nullable|string|max:2000',
        ]);

        UserReport::create([
            'reporter_id' => auth()->id(),
            'reported_id' => $user->id,
            'reason' => $validated['reason'],
            'explanation' => $validated['explanation'],
        ]);

        return back()->with('success', 'Report submitted.');
    }
}
