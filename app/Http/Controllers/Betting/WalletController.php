<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Services\BettingStatsService;
use App\Betting\Services\PlayWalletService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request, PlayWalletService $walletService)
    {
        $wallet = $walletService->getOrCreateWallet($request->user());

        return view('betting.wallet.show', compact('wallet'));
    }
}
