<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Models\Market;
use App\Betting\Services\MarketService;
use App\Betting\Services\SettlementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MarketAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Market::with(['event', 'creator'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $markets = $query->paginate(25);

        return view('admin.betting.markets.index', compact('markets'));
    }

    public function show(Market $market)
    {
        $market->load(['event', 'creator', 'challenger', 'currentVersion', 'participants', 'disputes']);
        $auditLogs = \App\Betting\Models\AuditLog::where('auditable_type', Market::class)
            ->where('auditable_id', $market->id)
            ->latest('created_at')
            ->limit(50)
            ->get();

        return view('admin.betting.markets.show', compact('market', 'auditLogs'));
    }

    public function approve(Market $market, MarketService $marketService)
    {
        try {
            $marketService->approve($market, auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Market approved and opened.');
    }

    public function reject(Request $request, Market $market, MarketService $marketService)
    {
        $validated = $request->validate(['reason' => 'required|string|max:1000']);

        try {
            $marketService->reject($market, auth()->user(), $validated['reason']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Market rejected.');
    }

    public function publishResult(Request $request, Market $market, SettlementService $settlementService)
    {
        $validated = $request->validate(['winning_outcome' => 'required|string|max:100']);

        try {
            $settlementService->publishMarketResult($market, $validated['winning_outcome'], auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Result published.');
    }

    public function settle(Market $market, SettlementService $settlementService)
    {
        try {
            $settlementService->settleMarket($market);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Market settled.');
    }

    public function void(Market $market, SettlementService $settlementService)
    {
        try {
            $settlementService->voidMarket($market, auth()->user(), 'admin_void');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Market voided and stakes refunded.');
    }
}
