<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password', [
            'meta_title' => 'Forgot Password | RoyalCasinoHub',
            'meta_description' => 'Reset your RoyalCasinoHub account password.',
            'noindex' => true,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
