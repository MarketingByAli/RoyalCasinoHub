<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Services\FaucetService;
use App\Betting\Services\PlayWalletService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request, PlayWalletService $walletService, FaucetService $faucet)
    {
        $wallet = $walletService->getOrCreateWallet($request->user());
        $entries = $wallet->ledgerEntries()->latest('id')->limit(50)->get();
        $canClaimFaucet = $faucet->canClaim($request->user());
        $nextFaucetAt = $faucet->nextClaimAt($request->user());

        return view('betting.wallet.show', compact('wallet', 'entries', 'canClaimFaucet', 'nextFaucetAt'));
    }

    public function claimFaucet(Request $request, FaucetService $faucet)
    {
        try {
            $faucet->claim($request->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('betting.faucet_claimed'));
    }
}
