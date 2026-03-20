<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrichmentQueue;
use Illuminate\Http\Request;

class EnrichmentAdminController extends Controller
{
    public function index()
    {
        $stats = [
            'pending' => EnrichmentQueue::pending()->count(),
            'processing' => EnrichmentQueue::where('status', 'processing')->count(),
            'done' => EnrichmentQueue::where('status', 'done')->count(),
            'failed' => EnrichmentQueue::where('status', 'failed')->count(),
        ];

        $recentJobs = EnrichmentQueue::with('casino')
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.enrichment.index', compact('stats', 'recentJobs'));
    }
}
