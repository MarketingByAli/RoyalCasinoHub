<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CasinoOwnerMiddleware;
use App\Http\Middleware\RedirectOldUrls;
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
            'casino.owner' => CasinoOwnerMiddleware::class,
            'redirect.old' => RedirectOldUrls::class,
        ]);
        $middleware->web(append: [
            RedirectOldUrls::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
