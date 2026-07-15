<?php

use App\Http\Controllers\Admin\Betting\AccountAdminController;
use App\Http\Controllers\Admin\Betting\DisputeAdminController;
use App\Http\Controllers\Admin\Betting\EventAdminController;
use App\Http\Controllers\Admin\Betting\MarketAdminController;
use App\Http\Controllers\Admin\Betting\UserReportAdminController;
use App\Http\Controllers\Admin\Betting\WalletAdminController;
use App\Http\Controllers\Betting\ChallengeController;
use App\Http\Controllers\Betting\DashboardController;
use App\Http\Controllers\Betting\DisputeController;
use App\Http\Controllers\Betting\InviteController;
use App\Http\Controllers\Betting\NotificationController;
use App\Http\Controllers\Betting\OnboardingController;
use App\Http\Controllers\Betting\ProfileController;
use App\Http\Controllers\Betting\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('challenges')->name('betting.')->group(function () {
    Route::get('/invite/{token}', [InviteController::class, 'show'])->name('invite.show');

    Route::middleware(['auth', 'active'])->group(function () {
        Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
        Route::post('/onboarding', [OnboardingController::class, 'store'])->middleware('throttle:5,1')->name('onboarding.store');

        Route::middleware('verified')->group(function () {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::get('/wallet', [WalletController::class, 'show'])->name('wallet');
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
            Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

            Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profiles.show');
            Route::get('/profile/edit/me', [ProfileController::class, 'edit'])->name('profiles.edit');
            Route::put('/profile/edit/me', [ProfileController::class, 'update'])->name('profiles.update');
            Route::post('/users/{user}/follow', [ProfileController::class, 'follow'])->middleware('throttle:30,1')->name('users.follow');
            Route::delete('/users/{user}/follow', [ProfileController::class, 'unfollow'])->name('users.unfollow');
            Route::post('/users/{user}/block', [ProfileController::class, 'block'])->middleware('throttle:20,1')->name('users.block');
            Route::post('/users/{user}/report', [ProfileController::class, 'report'])->middleware('throttle:10,1')->name('users.report');

            Route::middleware('betting.eligible')->group(function () {
                Route::get('/my', [ChallengeController::class, 'index'])->name('challenges.index');
                Route::get('/create', [ChallengeController::class, 'create'])->name('challenges.create');
                Route::post('/', [ChallengeController::class, 'store'])->middleware('throttle:10,1')->name('challenges.store');
                Route::get('/{market}', [ChallengeController::class, 'show'])->name('challenges.show');
                Route::post('/{market}/accept', [ChallengeController::class, 'accept'])->middleware('throttle:10,1')->name('challenges.accept');
                Route::post('/{market}/decline', [ChallengeController::class, 'decline'])->name('challenges.decline');
                Route::post('/{market}/cancel', [ChallengeController::class, 'cancel'])->name('challenges.cancel');
                Route::post('/{market}/dispute', [DisputeController::class, 'store'])->middleware('throttle:5,1')->name('disputes.store');
            });
        });
    });
});

Route::middleware(['auth', 'admin', 'active'])->prefix('admin/betting')->name('admin.betting.')->group(function () {
    Route::get('/events', [EventAdminController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventAdminController::class, 'create'])->name('events.create');
    Route::post('/events', [EventAdminController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventAdminController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventAdminController::class, 'update'])->name('events.update');
    Route::post('/events/{event}/result', [EventAdminController::class, 'publishResult'])->name('events.publish-result');

    Route::get('/markets', [MarketAdminController::class, 'index'])->name('markets.index');
    Route::get('/markets/{market}', [MarketAdminController::class, 'show'])->name('markets.show');
    Route::post('/markets/{market}/approve', [MarketAdminController::class, 'approve'])->name('markets.approve');
    Route::post('/markets/{market}/reject', [MarketAdminController::class, 'reject'])->name('markets.reject');
    Route::post('/markets/{market}/result', [MarketAdminController::class, 'publishResult'])->name('markets.publish-result');
    Route::post('/markets/{market}/settle', [MarketAdminController::class, 'settle'])->name('markets.settle');
    Route::post('/markets/{market}/void', [MarketAdminController::class, 'void'])->name('markets.void');

    Route::get('/disputes', [DisputeAdminController::class, 'index'])->name('disputes.index');
    Route::post('/disputes/{dispute}/resolve', [DisputeAdminController::class, 'resolve'])->name('disputes.resolve');

    Route::get('/wallets', [WalletAdminController::class, 'index'])->name('wallets.index');
    Route::post('/users/{user}/wallet-adjust', [WalletAdminController::class, 'adjust'])->name('wallets.adjust');

    Route::get('/users/{user}/account', [AccountAdminController::class, 'edit'])->name('accounts.edit');
    Route::put('/users/{user}/account', [AccountAdminController::class, 'update'])->name('accounts.update');

    Route::get('/reports', [UserReportAdminController::class, 'index'])->name('reports.index');
    Route::post('/reports/{report}/reviewed', [UserReportAdminController::class, 'updateStatus'])->name('reports.reviewed');
});
