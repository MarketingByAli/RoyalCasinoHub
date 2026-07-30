<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Enums\MarketStatus;
use App\Betting\Models\Market;
use App\Betting\Services\MarketService;
use App\Betting\Services\SettlementReversalService;
use App\Betting\Services\SettlementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarketAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Market::with(['event', 'creator'])->latest();

        if ($request->get('status') === 'stuck') {
            $query->where(function ($q) {
                $q->where(function ($open) {
                    $open->where('status', MarketStatus::Open)
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<', now());
                })->orWhere(function ($dispute) {
                    $dispute->where('status', MarketStatus::DisputeWindow)
                        ->whereNotNull('dispute_window_ends_at')
                        ->where('dispute_window_ends_at', '<', now());
                })->orWhere(function ($matched) {
                    $matched->where('status', MarketStatus::FullyMatched)
                        ->whereHas('event', fn ($event) => $event->where('start_at', '<', now()));
                });
            });
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        } elseif (! $request->has('status')) {
            $query->where('status', 'pending_review');
        }

        $markets = $query->paginate(25);
        $stuckCount = Market::query()
            ->where(function ($q) {
                $q->where(function ($open) {
                    $open->where('status', MarketStatus::Open)
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<', now());
                })->orWhere(function ($dispute) {
                    $dispute->where('status', MarketStatus::DisputeWindow)
                        ->whereNotNull('dispute_window_ends_at')
                        ->where('dispute_window_ends_at', '<', now());
                })->orWhere(function ($matched) {
                    $matched->where('status', MarketStatus::FullyMatched)
                        ->whereHas('event', fn ($event) => $event->where('start_at', '<', now()));
                });
            })
            ->count();

        return view('admin.betting.markets.index', compact('markets', 'stuckCount'));
    }

    public function show(Market $market, SettlementService $settlementService)
    {
        $market = $settlementService->ensureDisputeWindowFinalized($market);

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
        $validated = $request->validate([
            'winning_outcome' => ['required', 'string', 'max:100', Rule::in($market->outcome_options ?? [])],
        ]);

        try {
            $settlementService->publishMarketResult($market, $validated['winning_outcome'], auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Result published.');
    }

    public function reverse(Request $request, Market $market, SettlementReversalService $reversal)
    {
        $validated = $request->validate(['reason' => 'required|string|max:1000']);

        try {
            if ($request->boolean('void_after')) {
                $reversal->reverseAndVoid($market, auth()->user(), $validated['reason']);
            } else {
                $reversal->reverseSettlement($market, auth()->user(), $validated['reason']);
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Settlement reversed.');
    }

    public function settle(Request $request, Market $market, SettlementService $settlementService)
    {
        $force = $request->boolean('force_settle');

        try {
            $settlementService->settleMarket($market, force: $force);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $force ? 'Market force-settled.' : 'Market settled.');
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
