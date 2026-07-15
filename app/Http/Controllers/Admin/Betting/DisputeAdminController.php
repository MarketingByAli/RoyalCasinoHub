<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Models\Dispute;
use App\Betting\Services\SettlementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DisputeAdminController extends Controller
{
    public function index()
    {
        $disputes = Dispute::with(['market', 'user'])->where('status', 'open')->latest()->paginate(25);

        return view('admin.betting.disputes.index', compact('disputes'));
    }

    public function resolve(Request $request, Dispute $dispute, SettlementService $settlementService)
    {
        $validated = $request->validate([
            'resolution' => 'required|in:confirm,void',
            'note' => 'nullable|string|max:2000',
        ]);

        try {
            $settlementService->resolveDispute($dispute, auth()->user(), $validated['resolution'], $validated['note'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.betting.disputes.index')->with('success', 'Dispute resolved.');
    }
}
