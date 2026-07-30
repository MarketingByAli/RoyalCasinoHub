<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\DepositMethod;
use App\Betting\Models\DepositNotice;
use App\Betting\Models\WithdrawRequest;
use App\Betting\Services\PlayWalletService;
use App\Betting\Services\WalletFundingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request, PlayWalletService $walletService)
    {
        $wallet = $walletService->getOrCreateWallet($request->user());
        $entries = $wallet->ledgerEntries()->latest('id')->limit(50)->get();
        $depositMethods = DepositMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('coin_name')
            ->get();
        $pendingWithdrawals = WithdrawRequest::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->latest()
            ->get();
        $recentNotices = DepositNotice::where('user_id', $request->user()->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('betting.wallet.show', compact(
            'wallet',
            'entries',
            'depositMethods',
            'pendingWithdrawals',
            'recentNotices'
        ));
    }

    public function withdraw(Request $request, WalletFundingService $funding)
    {
        $validated = $request->validate([
            'deposit_method_id' => 'nullable|exists:betting_deposit_methods,id',
            'coin_name' => 'required_without:deposit_method_id|nullable|string|max:64',
            'network' => 'nullable|string|max:64',
            'destination_address' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'user_note' => 'nullable|string|max:1000',
        ]);

        try {
            $funding->submitWithdrawRequest($request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', __('betting.withdraw_submitted'));
    }

    public function notifyDeposit(Request $request, WalletFundingService $funding)
    {
        $validated = $request->validate([
            'deposit_method_id' => 'required|exists:betting_deposit_methods,id',
            'amount' => 'nullable|numeric|min:0.01',
            'tx_hash' => 'nullable|string|max:255',
            'user_note' => 'nullable|string|max:1000',
        ]);

        try {
            $funding->submitDepositNotice($request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', __('betting.deposit_notice_submitted'));
    }
}
