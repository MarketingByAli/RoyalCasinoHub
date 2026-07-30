<?php

namespace App\Http\Middleware;

use App\Betting\Enums\AccountState;
use App\Betting\Services\UserProfileService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBettingEligible
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if (! $user->bettingProfile) {
            return redirect()->route('betting.onboarding')->with('error', 'Complete betting profile setup first.');
        }

        // Self-heal accounts stuck on unverified after email was already verified
        // (e.g. onboarding cached a null bettingProfile before markEmailVerified ran).
        if ($user->bettingProfile->account_state === AccountState::Unverified) {
            app(UserProfileService::class)->markEmailVerified($user);
            $user->unsetRelation('bettingProfile');
        }

        if (! in_array($user->bettingProfile->account_state->value, ['play_only', 'verified'], true)) {
            abort(403, 'Your account is not eligible for betting.');
        }

        return $next($request);
    }
}
