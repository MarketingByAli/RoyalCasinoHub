<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CasinoReport;
use Illuminate\Http\Request;

class CasinoReportAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = CasinoReport::with(['casino', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(30);

        return view('admin.casino-reports.index', compact('reports'));
    }

    public function updateStatus(Request $request, CasinoReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,dismissed',
        ]);
        $report->status = $validated['status'];
        $report->save();

        return back()->with('success', 'Report updated.');
    }
}
