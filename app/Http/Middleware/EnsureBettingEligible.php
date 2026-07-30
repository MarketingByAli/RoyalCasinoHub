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
            try {
                app(UserProfileService::class)->markEmailVerified($user);
            } catch (\Throwable $e) {
                report($e);

                // Still unblock access if the grant/ledger write fails.
                $profile = $user->bettingProfile()->first();
                if ($profile && $profile->account_state === AccountState::Unverified) {
                    $profile->account_state = AccountState::PlayOnly;
                    $profile->save();
                }
            }

            $user->unsetRelation('bettingProfile');
        }

        $accountState = $user->bettingProfile?->account_state;
        $accountStateValue = $accountState instanceof AccountState
            ? $accountState->value
            : (string) $accountState;

        if (! in_array($accountStateValue, ['play_only', 'verified'], true)) {
            abort(403, 'Your account is not eligible for betting.');
        }

        return $next($request);
    }
}
