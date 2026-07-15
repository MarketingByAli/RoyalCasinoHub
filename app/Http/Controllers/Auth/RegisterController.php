<?php

namespace App\Http\Controllers\Auth;

use App\Betting\Services\UserProfileService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct(
        private UserProfileService $profileService
    ) {}

    public function showRegistrationForm()
    {
        return view('auth.register', [
            'meta_title' => 'Register | RoyalCasinoHub',
            'meta_description' => 'Create a RoyalCasinoHub account to review casinos, claim listings, and challenge friends.',
            'noindex' => true,
            'minimumAge' => config('betting.minimum_age', 18),
        ]);
    }

    public function register(Request $request)
    {
        if ($request->filled('company_website')) {
            abort(422);
        }

        $minimumAge = config('betting.minimum_age', 18);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:32|alpha_dash|unique:user_profiles,username',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:255|confirmed',
            'account_type' => 'required|in:user,casino_owner',
            'country' => 'required|string|size:2',
            'language' => 'required|string|max:10',
            'date_of_birth' => 'required|date|before:'.now()->subYears($minimumAge)->format('Y-m-d'),
            'accept_terms' => 'accepted',
            'accept_gambling_rules' => 'accepted',
            'accept_privacy' => 'accepted',
            'accept_responsible_gambling' => 'accepted',
            'accept_customer_funds' => 'accepted',
            'accept_marketing' => 'nullable|boolean',
        ], [
            'date_of_birth.before' => 'You must be at least '.$minimumAge.' years old.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['account_type'],
            'is_active' => true,
        ]);

        $this->profileService->createForUser($user, $validated);

        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
