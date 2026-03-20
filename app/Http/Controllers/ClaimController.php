<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\ClaimedListing;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'casino_id' => 'required|exists:casinos,id',
        ]);

        $user = $request->user();
        $casino = Casino::findOrFail($validated['casino_id']);

        if ($casino->status !== 'published') {
            abort(404);
        }

        $existing = ClaimedListing::where('casino_id', $casino->id)->where('user_id', $user->id)->first();
        if ($existing) {
            return back()->with('error', 'You have already submitted a claim for this casino.');
        }

        if ($casino->is_claimed) {
            return back()->with('error', 'This casino listing has already been claimed.');
        }

        $claim = new ClaimedListing(['casino_id' => $casino->id]);
        $claim->user_id = $user->id;
        $claim->status = 'pending';
        $claim->submitted_at = now();
        $claim->save();

        return back()->with('success', 'Your claim has been submitted. We will review it and get back to you.');
    }
}
