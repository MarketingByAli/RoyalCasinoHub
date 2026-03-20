<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Models\ClaimedListing;
use App\Models\EnrichmentQueue;
use App\Models\Review;

class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'casinos' => Casino::count(),
            'published_casinos' => Casino::published()->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'pending_claims' => ClaimedListing::pending()->count(),
            'pending_enrichment' => EnrichmentQueue::pending()->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
