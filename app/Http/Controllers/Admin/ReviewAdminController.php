<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
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
        $this->updateCasinoRating($review->casino);
        return back()->with('success', 'Review approved.');
    }

    public function reject(Review $review)
    {
        $review->status = 'rejected';
        $review->save();
        return back()->with('success', 'Review rejected.');
    }

    private function updateCasinoRating($casino): void
    {
        $stats = Review::where('casino_id', $casino->id)
            ->where('status', 'approved')
            ->selectRaw('AVG(rating) as avg, COUNT(*) as count')
            ->first();

        $casino->average_rating = $stats->avg ? round($stats->avg, 2) : null;
        $casino->reviews_count = $stats->count ?? 0;
        $casino->save();
    }
}
