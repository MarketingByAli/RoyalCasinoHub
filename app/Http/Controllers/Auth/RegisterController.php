<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'meta_title' => 'Register | RoyalCasinoHub',
            'meta_description' => 'Create a RoyalCasinoHub account to write casino reviews and claim your casino listing.',
            'noindex' => true,
        ]);
    }

    public function register(Request $request)
    {
        if ($request->filled('company_website')) {
            abort(422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:255|confirmed',
            'account_type' => 'required|in:user,casino_owner',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['account_type'],
            'is_active' => true,
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
