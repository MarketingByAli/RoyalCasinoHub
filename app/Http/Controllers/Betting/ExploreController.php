<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\BettingEvent;
use App\Betting\Models\Market;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    public function markets(Request $request)
    {
        $markets = Market::query()
            ->with(['event', 'creator.bettingProfile'])
            ->where('visibility', 'public')
            ->whereIn('status', [MarketStatus::Open, MarketStatus::PartiallyMatched])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->paginate(20);

        return view('betting.explore.markets', compact('markets'));
    }

    public function events()
    {
        $events = BettingEvent::approvedForBetting()
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->paginate(20);

        return view('betting.explore.events', compact('events'));
    }
}
