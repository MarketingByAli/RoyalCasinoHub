<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Models\DepositNotice;
use App\Betting\Models\WithdrawRequest;
use App\Betting\Services\WalletFundingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FundingAdminController extends Controller
{
    public function index()
    {
        $withdrawals = WithdrawRequest::with(['user.bettingProfile', 'method'])
            ->latest()
            ->paginate(20, ['*'], 'withdrawals_page');

        $deposits = DepositNotice::with(['user.bettingProfile', 'method'])
            ->latest()
            ->paginate(20, ['*'], 'deposits_page');

        return view('admin.betting.funding.index', compact('withdrawals', 'deposits'));
    }

    public function approveWithdraw(Request $request, WithdrawRequest $withdrawRequest, WalletFundingService $funding)
    {
        $validated = $request->validate(['admin_note' => 'nullable|string|max:2000']);

        try {
            $funding->approveWithdraw($withdrawRequest, auth()->user(), $validated['admin_note'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Withdrawal marked as paid.');
    }

    public function rejectWithdraw(Request $request, WithdrawRequest $withdrawRequest, WalletFundingService $funding)
    {
        $validated = $request->validate(['admin_note' => 'nullable|string|max:2000']);

        try {
            $funding->rejectWithdraw($withdrawRequest, auth()->user(), $validated['admin_note'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Withdrawal rejected and funds returned.');
    }

    public function creditDeposit(Request $request, DepositNotice $depositNotice, WalletFundingService $funding)
    {
        $validated = $request->validate([
            'credited_amount' => 'required|numeric|min:0.01',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        try {
            $funding->creditDepositNotice(
                $depositNotice,
                auth()->user(),
                (float) $validated['credited_amount'],
                $validated['admin_note'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Deposit credited to user wallet.');
    }

    public function rejectDeposit(Request $request, DepositNotice $depositNotice, WalletFundingService $funding)
    {
        $validated = $request->validate(['admin_note' => 'nullable|string|max:2000']);

        try {
            $funding->rejectDepositNotice($depositNotice, auth()->user(), $validated['admin_note'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Deposit notice rejected.');
    }
}
