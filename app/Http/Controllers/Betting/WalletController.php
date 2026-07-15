<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\Market;
use App\Betting\Services\PlayWalletService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request, PlayWalletService $walletService)
    {
        $wallet = $walletService->getOrCreateWallet($request->user());
        $entries = $wallet->ledgerEntries()->latest('id')->limit(50)->get();

        return view('betting.wallet.show', compact('wallet', 'entries'));
    }
}
