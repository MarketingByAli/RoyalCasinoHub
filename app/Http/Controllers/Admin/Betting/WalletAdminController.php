<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Services\PlayWalletService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'reason_code' => 'required|string|max:64',
            'market_id' => 'nullable|integer|exists:betting_markets,id',
            'confirm_reason' => 'required|string|same:reason',
            'confirm_username' => [
                'required',
                'string',
                Rule::in(array_values(array_filter([
                    $user->bettingProfile?->username,
                    $user->email,
                    (string) $user->id,
                ]))),
            ],
            'idempotency_key' => 'required|uuid',
        ]);

        $idempotencyKey = 'manual_adjust:'.$user->id.':'.$validated['idempotency_key'];
        $taggedReason = '['.$validated['reason_code'].'] '.$validated['reason']
            .(! empty($validated['market_id']) ? ' market:'.$validated['market_id'] : '');

        try {
            $walletService->manualAdjust(
                $user,
                (float) $validated['amount'],
                $taggedReason,
                auth()->user(),
                $idempotencyKey
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Wallet adjusted for user #'.$user->id.'.');
    }
}
