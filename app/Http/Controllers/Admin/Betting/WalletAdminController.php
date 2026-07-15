<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Services\PlayWalletService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalletAdminController extends Controller
{
    public function index()
    {
        return view('admin.betting.wallets.index');
    }

    public function adjust(Request $request, User $user, PlayWalletService $walletService)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'required|string|max:500',
            'confirm_reason' => 'required|string|same:reason',
        ]);

        $idempotencyKey = 'manual_adjust:'.$user->id.':'.Str::uuid();

        try {
            $walletService->manualAdjust(
                $user,
                (float) $validated['amount'],
                $validated['reason'],
                auth()->user(),
                $idempotencyKey
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Wallet adjusted.');
    }
}
