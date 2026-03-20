<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CasinoAdminController;
use App\Http\Controllers\Admin\ClaimAdminController;
use App\Http\Controllers\Admin\EnrichmentAdminController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\RedirectAdminController;
use App\Http\Controllers\Admin\ReviewAdminController;
use App\Http\Controllers\Admin\SeoAdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CasinoController;
use App\Http\Controllers\CasinoOwnerController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReviewController;
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
    ];
    $lines = ["User-agent: *"];
    foreach ($disallow as $path) {
        $lines[] = "Disallow: {$path}";
    }
    $lines[] = "Sitemap: " . config('app.url') . "/sitemap.xml";
    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
});

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemap-static.xml', [SitemapController::class, 'static']);
Route::get('/sitemap-countries.xml', [SitemapController::class, 'countries']);
Route::get('/sitemap-casinos-{page}.xml', [SitemapController::class, 'casinos'])->where('page', '[0-9]+');

Route::get('/', HomeController::class)->name('home');
Route::get('/casino/{slug}', [CasinoController::class, 'show'])->name('casino.show');
Route::get('/country/{slug}', [CountryController::class, 'show'])->name('country.show');
Route::get('/search', SearchController::class)->middleware('throttle:30,1')->name('search');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

Route::middleware('auth')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store'])->middleware('throttle:5,1')->name('reviews.store');
    Route::post('/claim', [ClaimController::class, 'store'])->middleware('throttle:3,1')->name('claim.store');

    Route::middleware('casino.owner')->prefix('my-listings')->name('casino-owner.')->group(function () {
        Route::get('/', [CasinoOwnerController::class, 'index'])->name('index');
        Route::get('/{casino}/edit', [CasinoOwnerController::class, 'edit'])->name('edit');
        Route::put('/{casino}', [CasinoOwnerController::class, 'update'])->name('update');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:3,1');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/casinos', [CasinoAdminController::class, 'index'])->name('casinos.index');
    Route::get('/casinos/{casino}/edit', [CasinoAdminController::class, 'edit'])->name('casinos.edit');
    Route::put('/casinos/{casino}', [CasinoAdminController::class, 'update'])->name('casinos.update');
    Route::post('/casinos/{casino}/toggle-status', [CasinoAdminController::class, 'toggleStatus'])->name('casinos.toggle-status');
    Route::post('/casinos/{casino}/queue-enrichment', [CasinoAdminController::class, 'queueEnrichment'])->name('casinos.queue-enrichment');
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');
    Route::post('/import/batch', [ImportController::class, 'storeBatch'])->name('import.store-batch');
    Route::get('/reviews', [ReviewAdminController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [ReviewAdminController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [ReviewAdminController::class, 'reject'])->name('reviews.reject');
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
