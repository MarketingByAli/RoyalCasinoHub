<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $casinos = $request->user()
            ->favoriteCasinos()
            ->withCount('approvedReviews')
            ->orderBy('user_casino_favorites.created_at', 'desc')
            ->paginate(24);

        return view('account.favorites', [
            'casinos' => $casinos,
            'meta_title' => 'Saved casinos | RoyalCasinoHub',
            'meta_description' => 'Your favorite online casinos.',
            'noindex' => true,
        ]);
    }

    public function toggle(Request $request, Casino $casino)
    {
        if ($casino->status !== 'published') {
            abort(404);
        }

        $user = $request->user();
        if ($user->favoriteCasinos()->whereKey($casino->id)->exists()) {
            $user->favoriteCasinos()->detach($casino->id);

            return back()->with('success', 'Removed from saved casinos.');
        }

        $user->favoriteCasinos()->attach($casino->id);

        return back()->with('success', 'Saved to your list.');
    }
}
