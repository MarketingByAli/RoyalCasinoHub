<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CasinoOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->role !== 'casino_owner') {
            abort(403, 'Unauthorized. Casino owner access required.');
        }

        return $next($request);
    }
}
