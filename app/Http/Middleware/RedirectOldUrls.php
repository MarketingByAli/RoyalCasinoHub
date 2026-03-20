<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RedirectOldUrls
{
    private const CACHE_KEY = 'redirects_map';

    private const CACHE_TTL = 3600;

    public function handle(Request $request, Closure $next): Response
    {
        $path = '/' . trim($request->path(), '/');

        $redirects = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Redirect::all()->keyBy('from_url');
        });

        $redirect = $redirects->get($path) ?? $redirects->get($request->url());

        if ($redirect) {
            Redirect::where('id', $redirect->id)->increment('hits');
            return redirect($redirect->to_url, $redirect->status_code);
        }

        return $next($request);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
