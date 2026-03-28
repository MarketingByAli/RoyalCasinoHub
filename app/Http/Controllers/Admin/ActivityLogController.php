<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->action.'%');
        }

        $logs = $query->paginate(50);

        return view('admin.activity.index', compact('logs'));
    }
}
