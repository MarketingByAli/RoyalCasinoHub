<?php

namespace App\Http\Controllers\Auth;

use App\Betting\Services\ReferralService;
use App\Betting\Services\UserProfileService;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function __construct(
        private UserProfileService $profileService,
        private ReferralService $referralService,
    ) {}

    public function notice(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('betting.dashboard')
            : view('auth.verify-email', [
                'meta_title' => 'Verify Email | RoyalCasinoHub',
                'meta_description' => 'Verify your email address to use all account features.',
                'noindex' => true,
            ]);
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        $this->profileService->markEmailVerified($request->user());
        $this->referralService->creditIfEligible($request->user()->fresh());

        return redirect()->route('betting.dashboard')->with('success', 'Your email has been verified. Play points have been added to your wallet.');
    }

    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('betting.dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
