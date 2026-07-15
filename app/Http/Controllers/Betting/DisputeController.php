<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\Market;
use App\Betting\Services\SettlementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function store(Request $request, Market $market, SettlementService $settlementService)
    {
        $validated = $request->validate([
            'reason_category' => 'required|string|max:64',
            'explanation' => 'nullable|string|max:2000',
        ]);

        try {
            $settlementService->openDispute(
                $market,
                $request->user(),
                $validated['reason_category'],
                $validated['explanation'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Dispute submitted. Our team will review.');
    }
}
