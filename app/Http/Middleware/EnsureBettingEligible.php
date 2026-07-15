<?php

namespace App\Http\Middleware;

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

        if (! in_array($user->bettingProfile->account_state->value, ['play_only', 'verified'], true)) {
            abort(403, 'Your account is not eligible for betting.');
        }

        return $next($request);
    }
}
