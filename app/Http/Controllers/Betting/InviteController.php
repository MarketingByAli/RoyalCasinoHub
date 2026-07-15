<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\Market;
use App\Http\Controllers\Controller;

class InviteController extends Controller
{
    public function show(string $token)
    {
        $market = Market::where('invite_token', $token)
            ->with(['event', 'creator.bettingProfile', 'currentVersion'])
            ->firstOrFail();

        if ($market->status !== MarketStatus::Open) {
            return view('betting.invite.closed', compact('market'));
        }

        if ($market->expires_at && $market->expires_at->isPast()) {
            return view('betting.invite.expired', compact('market'));
        }

        session()->put('betting.invite_tokens.'.$market->id, $market->invite_token);

        return view('betting.invite.show', compact('market'));
    }
}
