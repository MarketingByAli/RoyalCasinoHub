<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewVote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReviewVoteController extends Controller
{
    public function store(Request $request, Review $review)
    {
        Gate::authorize('vote', $review);

        $validated = $request->validate([
            'helpful' => 'required|boolean',
        ]);
        $wantHelpful = $validated['helpful'];

        DB::transaction(function () use ($review, $wantHelpful) {
            $locked = Review::lockForUpdate()->find($review->id);
            if (! $locked || $locked->status !== 'approved') {
                abort(404);
            }

            $userId = auth()->id();
            $existing = ReviewVote::where('review_id', $locked->id)->where('user_id', $userId)->first();

            if ($existing && $existing->helpful === $wantHelpful) {
                if ($existing->helpful) {
                    $locked->decrement('helpful_up_count');
                } else {
                    $locked->decrement('helpful_down_count');
                }
                $existing->delete();
            } elseif ($existing) {
                if ($existing->helpful) {
                    $locked->decrement('helpful_up_count');
                    $locked->increment('helpful_down_count');
                } else {
                    $locked->decrement('helpful_down_count');
                    $locked->increment('helpful_up_count');
                }
                $existing->helpful = $wantHelpful;
                $existing->save();
            } else {
                ReviewVote::create([
                    'review_id' => $locked->id,
                    'user_id' => $userId,
                    'helpful' => $wantHelpful,
                ]);
                if ($wantHelpful) {
                    $locked->increment('helpful_up_count');
                } else {
                    $locked->increment('helpful_down_count');
                }
            }

            User::find($locked->user_id)?->recalculateReviewerCredibility();
        });

        return back();
    }
}
