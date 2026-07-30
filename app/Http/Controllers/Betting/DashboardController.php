<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\BettingNotification;
use App\Betting\Models\Market;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $wallet = $user->bettingWallet;

        $recentMarkets = Market::query()
            ->with('event')
            ->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                    ->orWhere('challenger_id', $user->id)
                    ->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id));
            })
            ->latest()
            ->limit(5)
            ->get();

        $unreadCount = BettingNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('betting.dashboard', compact('wallet', 'recentMarkets', 'unreadCount'));
    }
}
