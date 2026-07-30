<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Services\BettingDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BettingDashboardService $dashboard)
    {
        $kpis = $dashboard->kpis();
        $mismatchCount = (int) Cache::get('betting.wallet_mismatches', 0);
        $users = collect();

        if ($request->filled('q')) {
            $users = $dashboard->searchUsers($request->string('q')->toString());
        }

        return view('admin.betting.dashboard', compact('kpis', 'mismatchCount', 'users'));
    }
}
