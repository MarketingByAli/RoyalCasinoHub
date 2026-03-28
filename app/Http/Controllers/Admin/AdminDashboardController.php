<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Models\ClaimedListing;
use App\Models\EnrichmentQueue;
use App\Models\Review;
use Illuminate\Support\Carbon;

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

        $reviewTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $reviewTrend[$day->toDateString()] = Review::whereDate('created_at', $day)->count();
        }

        return view('admin.dashboard', compact('stats', 'reviewTrend'));
    }
}
