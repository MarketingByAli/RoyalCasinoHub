<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index()
    {
        return view('account.index', [
            'meta_title' => 'My Account | RoyalCasinoHub',
            'meta_description' => 'Manage your RoyalCasinoHub profile and activity.',
            'noindex' => true,
        ]);
    }

    public function submittedListings()
    {
        $casinos = Auth::user()->submittedCasinos()->latest()->paginate(15);

        return view('account.submitted-listings', [
            'casinos' => $casinos,
            'meta_title' => 'Submitted listings | RoyalCasinoHub',
            'meta_description' => 'Casino listings you have submitted.',
            'noindex' => true,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $settings = array_merge($request->user()->settings ?? [], [
            'digest_weekly' => $request->boolean('digest_weekly'),
            'marketing_emails' => $request->boolean('marketing_emails'),
        ]);

        $request->user()->update(['settings' => $settings]);

        return back()->with('success', 'Preferences saved.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->reviews()->update([
                'title' => 'Review removed',
                'content' => 'This review was removed when the author deleted their account.',
            ]);

            $user->name = 'Deleted user';
            $user->email = 'deleted-'.$user->id.'-'.Str::random(8).'@invalid.local';
            $user->password = Hash::make(Str::random(64));
            $user->save();
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been deleted.');
    }

    public function editProfile()
    {
        return view('account.profile', [
            'meta_title' => 'Profile | RoyalCasinoHub',
            'meta_description' => 'Update your profile.',
            'noindex' => true,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $request->user()->update(['name' => $validated['name']]);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated.');
    }

    public function reviews(Request $request)
    {
        $reviews = $request->user()
            ->reviews()
            ->with('casino')
            ->latest()
            ->paginate(15);

        return view('account.reviews', [
            'reviews' => $reviews,
            'meta_title' => 'My Reviews | RoyalCasinoHub',
            'meta_description' => 'Your casino reviews.',
            'noindex' => true,
        ]);
    }

    public function claims(Request $request)
    {
        $claims = $request->user()
            ->claimedListings()
            ->with('casino')
            ->latest('submitted_at')
            ->paginate(15);

        return view('account.claims', [
            'claims' => $claims,
            'meta_title' => 'My Claims | RoyalCasinoHub',
            'meta_description' => 'Your listing claims.',
            'noindex' => true,
        ]);
    }
}
