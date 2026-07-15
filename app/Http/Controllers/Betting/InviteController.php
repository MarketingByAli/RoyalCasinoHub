<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\Market;
use App\Http\Controllers\Controller;

class InviteController extends Controller
{
    public function show(string $token)
    {
        $market = Market::where('invite_token', $token)
            ->with(['event', 'creator.bettingProfile', 'currentVersion'])
            ->firstOrFail();

        if ($market->expires_at && $market->expires_at->isPast() && $market->status->value === 'open') {
            return view('betting.invite.expired', compact('market'));
        }

        return view('betting.invite.show', compact('market'));
    }
}
