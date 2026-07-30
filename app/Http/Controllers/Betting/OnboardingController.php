<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Services\ReferralService;
use App\Betting\Services\UserProfileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function show(Request $request)
    {
        if (auth()->user()->bettingProfile) {
            return redirect()->route('betting.dashboard');
        }

        if ($ref = $request->query('ref')) {
            session(['betting.referral_code' => strtoupper((string) $ref)]);
        }

        return view('betting.onboarding', [
            'referralCode' => session('betting.referral_code'),
        ]);
    }

    public function store(Request $request, UserProfileService $profileService, ReferralService $referralService)
    {
        if (auth()->user()->bettingProfile) {
            return redirect()->route('betting.dashboard');
        }

        $minimumAge = config('betting.minimum_age', 18);

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:32|alpha_dash|unique:user_profiles,username',
            'display_name' => 'nullable|string|max:255',
            'country' => 'required|string|size:2',
            'language' => 'required|in:en',
            'date_of_birth' => 'required|date|before:'.now()->subYears($minimumAge)->format('Y-m-d'),
            'accept_terms' => 'accepted',
            'accept_gambling_rules' => 'accepted',
            'accept_privacy' => 'accepted',
            'accept_responsible_gambling' => 'accepted',
            'accept_customer_funds' => 'accepted',
            'accept_marketing' => 'nullable|boolean',
            'referral_code' => 'nullable|string|max:32',
        ], [
            'date_of_birth.before' => 'You must be at least '.$minimumAge.' years old.',
        ]);

        $profileService->createForUser(auth()->user(), $validated);

        $code = $validated['referral_code'] ?? session('betting.referral_code');
        $referralService->attributeReferral(auth()->user()->fresh(), $code);

        if (auth()->user()->hasVerifiedEmail()) {
            $profileService->markEmailVerified(auth()->user());
            $referralService->creditIfEligible(auth()->user()->fresh());
        }

        session()->forget('betting.referral_code');

        return redirect()->route('betting.dashboard')->with('success', __('betting.profile_created'));
    }
}
