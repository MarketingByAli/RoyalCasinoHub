<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Services\UserProfileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function show()
    {
        if (auth()->user()->bettingProfile) {
            return redirect()->route('betting.dashboard');
        }

        return view('betting.onboarding');
    }

    public function store(Request $request, UserProfileService $profileService)
    {
        if (auth()->user()->bettingProfile) {
            return redirect()->route('betting.dashboard');
        }

        $minimumAge = config('betting.minimum_age', 18);

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:32|alpha_dash|unique:user_profiles,username',
            'display_name' => 'nullable|string|max:255',
            'country' => 'required|string|size:2',
            'language' => 'required|string|max:10',
            'date_of_birth' => 'required|date|before:'.now()->subYears($minimumAge)->format('Y-m-d'),
            'accept_terms' => 'accepted',
            'accept_gambling_rules' => 'accepted',
            'accept_privacy' => 'accepted',
            'accept_responsible_gambling' => 'accepted',
            'accept_customer_funds' => 'accepted',
            'accept_marketing' => 'nullable|boolean',
        ], [
            'date_of_birth.before' => 'You must be at least '.$minimumAge.' years old.',
        ]);

        $profileService->createForUser(auth()->user(), $validated);

        if (auth()->user()->hasVerifiedEmail()) {
            $profileService->markEmailVerified(auth()->user());
        }

        return redirect()->route('betting.dashboard')->with('success', 'Betting profile created.');
    }
}
