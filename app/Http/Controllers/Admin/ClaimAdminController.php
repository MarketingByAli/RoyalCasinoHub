<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClaimedListing;
use App\Notifications\ClaimModeratedNotification;
use Illuminate\Http\Request;

class ClaimAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ClaimedListing::with(['casino', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $claims = $query->latest()->paginate(25);

        return view('admin.claims.index', compact('claims'));
    }

    public function approve(ClaimedListing $claim)
    {
        $claim->status = 'approved';
        $claim->approved_at = now();
        $claim->save();

        $casino = $claim->casino;
        $casino->is_claimed = true;
        $casino->claimed_by_user_id = $claim->user_id;
        $casino->save();

        $user = $claim->user;
        if ($user->role === 'user') {
            $user->role = 'casino_owner';
            $user->save();
        }

        $claim->loadMissing('user', 'casino');
        if ($claim->user) {
            $claim->user->notify(new ClaimModeratedNotification($claim, 'approved'));
        }

        return back()->with('success', 'Claim approved.');
    }

    public function reject(Request $request, ClaimedListing $claim)
    {
        $request->validate(['notes' => 'nullable|string|max:2000']);

        $claim->status = 'rejected';
        $claim->notes = $request->input('notes');
        $claim->save();

        $claim->loadMissing('user', 'casino');
        if ($claim->user) {
            $claim->user->notify(new ClaimModeratedNotification($claim, 'rejected'));
        }

        return back()->with('success', 'Claim rejected.');
    }
}
