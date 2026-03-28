<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewReplyController extends Controller
{
    public function store(Request $request, Review $review)
    {
        $review->loadMissing('casino');
        Gate::authorize('reply', $review);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        ReviewReply::updateOrCreate(
            [
                'review_id' => $review->id,
                'user_id' => $request->user()->id,
            ],
            [
                'body' => $validated['body'],
                'status' => 'approved',
            ]
        );

        return back()->with('success', 'Your reply was posted.');
    }
}
