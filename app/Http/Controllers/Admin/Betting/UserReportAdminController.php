<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Models\UserReport;
use App\Http\Controllers\Controller;

class UserReportAdminController extends Controller
{
    public function index()
    {
        $reports = UserReport::with(['reporter', 'reported'])
            ->where('status', 'open')
            ->latest()
            ->paginate(25);

        return view('admin.betting.reports.index', compact('reports'));
    }

    public function updateStatus(UserReport $report)
    {
        $report->update(['status' => 'reviewed']);

        return back()->with('success', 'Report marked reviewed.');
    }
}
