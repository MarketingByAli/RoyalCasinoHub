<?php

namespace App\Providers;

use App\Betting\Models\Market;
use App\Betting\Policies\MarketPolicy;
use App\Models\Casino;
use App\Models\Review;
use App\Policies\CasinoPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(Casino::class, CasinoPolicy::class);
        Gate::policy(Market::class, MarketPolicy::class);

        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('reviews', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('flags', fn (Request $request) => Limit::perMinute(20)->by($request->user()?->id ?: $request->ip()));
    }
}
