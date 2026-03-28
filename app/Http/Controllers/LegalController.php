<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    public function terms(Request $request)
    {
        return view('legal.terms', [
            'meta_title' => 'Terms of Use | RoyalCasinoHub',
            'meta_description' => 'Terms of use for RoyalCasinoHub.',
            'canonical' => url('/terms'),
            'noindex' => true,
        ]);
    }

    public function privacy(Request $request)
    {
        return view('legal.privacy', [
            'meta_title' => 'Privacy Policy | RoyalCasinoHub',
            'meta_description' => 'Privacy policy for RoyalCasinoHub.',
            'canonical' => url('/privacy'),
            'noindex' => true,
        ]);
    }
}
