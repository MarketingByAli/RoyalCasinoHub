<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::approved()
            ->with(['casino', 'user'])
            ->latest()
            ->paginate(20);

        $metaTitle = 'Latest Casino Reviews | RoyalCasinoHub';
        $metaDescription = 'Read the latest user reviews of online casinos. Honest ratings and experiences from real players.';

        $canonical = $reviews->currentPage() === 1
            ? url('/reviews')
            : url("/reviews?page={$reviews->currentPage()}");

        $prevPage = $reviews->currentPage() > 1
            ? url('/reviews').($reviews->currentPage() > 2 ? '?page='.($reviews->currentPage() - 1) : '')
            : null;
        $nextPage = $reviews->hasMorePages()
            ? url('/reviews?page='.($reviews->currentPage() + 1))
            : null;

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Reviews', 'item' => null],
            ],
        ];

        return view('reviews.index', [
            'reviews' => $reviews,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'canonical' => $canonical,
            'breadcrumbSchema' => $breadcrumbSchema,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Review::class);

        $validated = $request->validate([
            'casino_id' => 'required|exists:casinos,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'dimension_ratings' => 'nullable|array',
            'dimension_ratings.trust' => 'nullable|integer|min:1|max:5',
            'dimension_ratings.games' => 'nullable|integer|min:1|max:5',
            'dimension_ratings.support' => 'nullable|integer|min:1|max:5',
            'dimension_ratings.payments' => 'nullable|integer|min:1|max:5',
            'dimension_ratings.bonuses' => 'nullable|integer|min:1|max:5',
        ]);

        $dimensionRatings = collect($validated['dimension_ratings'] ?? [])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v) => (int) $v)
            ->all();
        unset($validated['dimension_ratings']);

        $casino = Casino::findOrFail($validated['casino_id']);
        if ($casino->status !== 'published') {
            abort(404);
        }

        $user = $request->user();

        $existingReview = Review::where('casino_id', $casino->id)->where('user_id', $user->id)->first();
        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this casino.');
        }

        $review = new Review($validated);
        $review->casino_id = $casino->id;
        $review->user_id = $user->id;
        $review->status = 'pending';
        $review->dimension_ratings = $dimensionRatings !== [] ? $dimensionRatings : null;
        $review->save();

        return back()->with('success', 'Your review has been submitted and is pending approval.');
    }
}
