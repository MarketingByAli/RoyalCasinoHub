<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CasinoAdminController;
use App\Http\Controllers\Admin\ClaimAdminController;
use App\Http\Controllers\Admin\EnrichmentAdminController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\RedirectAdminController;
use App\Http\Controllers\Admin\ReviewAdminController;
use App\Http\Controllers\Admin\SeoAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CasinoController;
use App\Http\Controllers\CasinoListingSubmissionController;
use App\Http\Controllers\CasinoOwnerController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CasinoReportAdminController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CasinoReportPublicController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewReplyController;
use App\Http\Controllers\ReviewReportController;
use App\Http\Controllers\ReviewVoteController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', function () {
    $disallow = [
        '/admin',
        '/login',
        '/register',
        '/admin/import',
        '/api',
        '/forgot-password',
        '/reset-password',
        '/verify-email',
        '/submit-listing',
    ];
    $lines = ['User-agent: *'];
    foreach ($disallow as $path) {
        $lines[] = "Disallow: {$path}";
    }
    $lines[] = 'Sitemap: '.config('app.url').'/sitemap.xml';

    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
});

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemap-static.xml', [SitemapController::class, 'static']);
Route::get('/sitemap-countries.xml', [SitemapController::class, 'countries']);
Route::get('/sitemap-casinos-{page}.xml', [SitemapController::class, 'casinos'])->where('page', '[0-9]+');

Route::get('/', HomeController::class)->name('home');
Route::get('/casino/{slug}', [CasinoController::class, 'show'])->name('casino.show');
Route::get('/country/{slug}', [CountryController::class, 'show'])->name('country.show');
Route::get('/compare', [CompareController::class, 'show'])->name('compare.show');
Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [PostController::class, 'show'])->name('blog.show');
Route::get('/search', SearchController::class)->middleware('throttle:30,1')->name('search');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

Route::post('/cookie-consent', [CookieConsentController::class, 'store'])->name('cookie-consent.store');

Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/reviews/{review}/vote', [ReviewVoteController::class, 'store'])
        ->middleware(['throttle:60,1', 'verified'])
        ->name('reviews.vote');
    Route::post('/reviews/{review}/report', [ReviewReportController::class, 'store'])
        ->middleware(['throttle:20,1', 'verified'])
        ->name('reviews.report');
    Route::post('/reviews/{review}/replies', [ReviewReplyController::class, 'store'])
        ->middleware(['throttle:20,1', 'verified'])
        ->name('reviews.replies.store');
    Route::post('/reviews', [ReviewController::class, 'store'])
        ->middleware(['throttle:5,1', 'verified'])
        ->name('reviews.store');
    Route::post('/claim', [ClaimController::class, 'store'])
        ->middleware(['throttle:3,1', 'verified'])
        ->name('claim.store');

    Route::middleware(['casino.owner', 'verified'])->prefix('my-listings')->name('casino-owner.')->group(function () {
        Route::get('/', [CasinoOwnerController::class, 'index'])->name('index');
        Route::get('/{casino}/analytics', [CasinoOwnerController::class, 'analytics'])->name('analytics');
        Route::get('/{casino}/edit', [CasinoOwnerController::class, 'edit'])->name('edit');
        Route::put('/{casino}', [CasinoOwnerController::class, 'update'])->name('update');
    });

    Route::middleware('verified')->prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/submitted-listings', [AccountController::class, 'submittedListings'])->name('submitted-listings');
        Route::get('/profile', [AccountController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
        Route::put('/settings', [AccountController::class, 'updateSettings'])->name('settings');
        Route::delete('/', [AccountController::class, 'destroy'])->name('destroy');
        Route::get('/reviews', [AccountController::class, 'reviews'])->name('reviews');
        Route::get('/claims', [AccountController::class, 'claims'])->name('claims');
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    });

    Route::post('/favorites/{casino}', [FavoriteController::class, 'toggle'])
        ->middleware('throttle:30,1')
        ->name('favorites.toggle');

    Route::post('/casinos/{casino}/report', [CasinoReportPublicController::class, 'store'])
        ->middleware(['throttle:10,1', 'verified'])
        ->name('casinos.report');

    Route::get('/submit-listing', [CasinoListingSubmissionController::class, 'create'])
        ->name('casino-listings.create');
    Route::post('/submit-listing', [CasinoListingSubmissionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('casino-listings.store');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:register');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'admin', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
    Route::put('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
    Route::get('/casinos', [CasinoAdminController::class, 'index'])->name('casinos.index');
    Route::get('/casino-directory', [CasinoAdminController::class, 'directoryInsights'])->name('casino-directory');
    Route::get('/casinos/create', [CasinoAdminController::class, 'create'])->name('casinos.create');
    Route::post('/casinos', [CasinoAdminController::class, 'store'])->name('casinos.store');
    Route::get('/casinos/{casino}/edit', [CasinoAdminController::class, 'edit'])->name('casinos.edit');
    Route::put('/casinos/{casino}', [CasinoAdminController::class, 'update'])->name('casinos.update');
    Route::post('/casinos/{casino}/toggle-status', [CasinoAdminController::class, 'toggleStatus'])->name('casinos.toggle-status');
    Route::post('/casinos/{casino}/queue-enrichment', [CasinoAdminController::class, 'queueEnrichment'])->name('casinos.queue-enrichment');
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');
    Route::post('/import/batch', [ImportController::class, 'storeBatch'])->name('import.store-batch');
    Route::get('/reviews', [ReviewAdminController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/bulk-approve', [ReviewAdminController::class, 'bulkApprove'])->name('reviews.bulk-approve');
    Route::post('/reviews/bulk-reject', [ReviewAdminController::class, 'bulkReject'])->name('reviews.bulk-reject');
    Route::post('/reviews/{review}/approve', [ReviewAdminController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [ReviewAdminController::class, 'reject'])->name('reviews.reject');
    Route::put('/reviews/{review}/note', [ReviewAdminController::class, 'updateNote'])->name('reviews.note');
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
    Route::get('/casino-reports', [CasinoReportAdminController::class, 'index'])->name('casino-reports.index');
    Route::post('/casino-reports/{report}', [CasinoReportAdminController::class, 'updateStatus'])->name('casino-reports.update');
    Route::get('/exports/users', [ExportController::class, 'users'])->name('exports.users');
    Route::get('/exports/casinos', [ExportController::class, 'casinos'])->name('exports.casinos');
    Route::get('/exports/reviews', [ExportController::class, 'reviews'])->name('exports.reviews');
    Route::get('/exports/claims', [ExportController::class, 'claims'])->name('exports.claims');
    Route::get('/claims', [ClaimAdminController::class, 'index'])->name('claims.index');
    Route::post('/claims/{claim}/approve', [ClaimAdminController::class, 'approve'])->name('claims.approve');
    Route::post('/claims/{claim}/reject', [ClaimAdminController::class, 'reject'])->name('claims.reject');
    Route::get('/redirects', [RedirectAdminController::class, 'index'])->name('redirects.index');
    Route::post('/redirects', [RedirectAdminController::class, 'store'])->name('redirects.store');
    Route::delete('/redirects/{redirect}', [RedirectAdminController::class, 'destroy'])->name('redirects.destroy');
    Route::get('/seo', [SeoAdminController::class, 'index'])->name('seo.index');
    Route::put('/seo', [SeoAdminController::class, 'update'])->name('seo.update');
    Route::get('/enrichment', [EnrichmentAdminController::class, 'index'])->name('enrichment.index');
});
