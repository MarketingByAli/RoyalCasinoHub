<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\CasinoDailyView;
use App\Models\ReviewVote;
use App\Services\SeoService;
use Illuminate\Support\Facades\Auth;

class CasinoController extends Controller
{
    public function show(string $slug, SeoService $seoService)
    {
        $casino = Casino::published()
            ->with([
                'activeOffers',
                'approvedReviews' => fn ($q) => $q->with(['user', 'ownerReply.user', 'casino'])->latest()->limit(10),
                'news' => fn ($q) => $q->latest('published_at')->limit(5),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        CasinoDailyView::firstOrCreate(
            [
                'casino_id' => $casino->id,
                'day' => now()->toDateString(),
            ],
            ['views' => 0]
        )->increment('views');

        $schema = $seoService->generateCasinoSchema($casino, $casino->approvedReviews);

        $userReviewVotes = collect();
        if (Auth::check()) {
            $ids = $casino->approvedReviews->pluck('id');
            if ($ids->isNotEmpty()) {
                $userReviewVotes = ReviewVote::where('user_id', Auth::id())
                    ->whereIn('review_id', $ids)
                    ->pluck('helpful', 'review_id');
            }
        }
        $breadcrumbSchema = $seoService->generateBreadcrumbSchema([
            ['name' => 'Home', 'url' => url('/')],
            ['name' => $casino->country, 'url' => url("/country/{$casino->country_slug}")],
            ['name' => $casino->name, 'url' => null],
        ]);

        $metaTitle = $casino->meta_title ?: $seoService->getMetaTitle(null, $casino);
        $metaDescription = $casino->meta_description ?: $seoService->getMetaDescription(null, $casino);
        $canonical = $casino->canonical_url ?: url("/casino/{$casino->slug}");
        $robots = $casino->robots ?: 'index,follow';
        $ogImage = $casino->effectiveScreenshotUrl() ?: ($casino->logo_url ?: null);

        $relatedCasinos = Casino::published()
            ->where('id', '!=', $casino->id)
            ->where('country_slug', $casino->country_slug)
            ->orderByDesc('average_rating')
            ->orderByDesc('reviews_count')
            ->limit(6)
            ->get();

        $isFavorite = Auth::check()
            && Auth::user()->favoriteCasinos()->whereKey($casino->id)->exists();

        return view('casino.show', [
            'casino' => $casino,
            'schema' => $schema,
            'breadcrumbSchema' => $breadcrumbSchema,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonical' => $canonical,
            'robots' => $robots,
            'ogImage' => $ogImage,
            'userReviewVotes' => $userReviewVotes,
            'relatedCasinos' => $relatedCasinos,
            'isFavorite' => $isFavorite,
        ]);
    }
}
