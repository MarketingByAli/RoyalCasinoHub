<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'essential' => 'sometimes|boolean',
        ]);

        $request->session()->put('cookie_consent', true);
        $request->session()->put('cookie_consent_at', now()->toIso8601String());

        return back();
    }
}
