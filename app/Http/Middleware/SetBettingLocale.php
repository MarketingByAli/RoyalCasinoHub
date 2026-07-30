<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetBettingLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'en';
        $user = $request->user();

        if ($user?->bettingProfile?->language === 'en') {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
