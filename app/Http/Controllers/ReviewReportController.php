<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewReportController extends Controller
{
    public function store(Request $request, Review $review)
    {
        Gate::authorize('report', $review);

        $validated = $request->validate([
            'reason' => 'required|string|in:spam,inappropriate,misleading,other',
            'details' => 'nullable|string|max:2000',
        ]);

        $userId = $request->user()->id;
        if (ReviewReport::where('review_id', $review->id)->where('user_id', $userId)->exists()) {
            return back()->with('error', 'You have already reported this review.');
        }

        ReviewReport::create([
            'review_id' => $review->id,
            'user_id' => $userId,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thank you — your report was submitted.');
    }
}
