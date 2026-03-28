<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Models\Review;
use App\Notifications\ReviewModeratedNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ReviewAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['casino', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reviews = $query->latest()->paginate(25);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(Review $review)
    {
        $review->status = 'approved';
        $review->save();
        $this->updateCasinoRating($review->casino->fresh());
        $review->loadMissing('user', 'casino');
        if ($review->user) {
            $review->user->recalculateReviewerCredibility();
            $review->user->notify(new ReviewModeratedNotification($review, 'approved'));
        }
        ActivityLogger::log('review.approved', $review);

        return back()->with('success', 'Review approved.');
    }

    public function reject(Review $review)
    {
        $review->status = 'rejected';
        $review->save();
        $review->loadMissing('user', 'casino');
        if ($review->user) {
            $review->user->notify(new ReviewModeratedNotification($review, 'rejected'));
        }
        ActivityLogger::log('review.rejected', $review);

        return back()->with('success', 'Review rejected.');
    }

    public function updateNote(Request $request, Review $review)
    {
        $validated = $request->validate([
            'admin_internal_note' => 'nullable|string|max:5000',
        ]);
        $review->admin_internal_note = $validated['admin_internal_note'] ?? null;
        $review->save();

        return back()->with('success', 'Internal note saved.');
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'review_ids' => 'required|array|max:100',
            'review_ids.*' => 'integer|exists:reviews,id',
        ]);

        $count = 0;
        foreach ($validated['review_ids'] as $id) {
            $review = Review::find($id);
            if ($review && $review->status === 'pending') {
                $review->status = 'approved';
                $review->save();
                $this->updateCasinoRating($review->casino->fresh());
                $review->user?->recalculateReviewerCredibility();
                $review->user?->notify(new ReviewModeratedNotification($review->fresh(['casino']), 'approved'));
                ActivityLogger::log('review.approved', $review);
                $count++;
            }
        }

        return back()->with('success', "Approved {$count} review(s).");
    }

    public function bulkReject(Request $request)
    {
        $validated = $request->validate([
            'review_ids' => 'required|array|max:100',
            'review_ids.*' => 'integer|exists:reviews,id',
        ]);

        $count = 0;
        foreach ($validated['review_ids'] as $id) {
            $review = Review::find($id);
            if ($review && $review->status === 'pending') {
                $review->status = 'rejected';
                $review->save();
                $review->user?->notify(new ReviewModeratedNotification($review->fresh(['casino']), 'rejected'));
                ActivityLogger::log('review.rejected', $review);
                $count++;
            }
        }

        return back()->with('success', "Rejected {$count} review(s).");
    }

    private function updateCasinoRating(Casino $casino): void
    {
        $stats = Review::where('casino_id', $casino->id)
            ->where('status', 'approved')
            ->selectRaw('AVG(rating) as avg, COUNT(*) as count')
            ->first();

        $casino->average_rating = $stats->avg ? round((float) $stats->avg, 2) : null;
        $casino->reviews_count = (int) ($stats->count ?? 0);
        $casino->recalculateDimensionAverages();
        $casino->save();
    }
}
