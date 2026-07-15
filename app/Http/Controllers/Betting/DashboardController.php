<?php

namespace App\Http\Controllers\Betting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $wallet = $user->bettingWallet;
        $recentMarkets = $user->createdMarkets()->with('event')->latest()->limit(5)->get();

        return view('betting.dashboard', compact('wallet', 'recentMarkets'));
    }
}
