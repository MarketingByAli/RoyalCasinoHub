<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CasinoOwnerMiddleware;
use App\Http\Middleware\EnsureBettingEligible;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RedirectOldUrls;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'betting.eligible' => EnsureBettingEligible::class,
            'casino.owner' => CasinoOwnerMiddleware::class,
            'redirect.old' => RedirectOldUrls::class,
            'verified' => EnsureEmailIsVerified::class,
            'active' => EnsureUserIsActive::class,
        ]);
        $middleware->web(append: [
            RedirectOldUrls::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
