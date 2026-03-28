<?php

namespace App\Http\Controllers;

use App\Models\Casino;
use App\Models\CasinoReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CasinoReportPublicController extends Controller
{
    public function store(Request $request, Casino $casino)
    {
        if ($casino->status !== 'published') {
            abort(404);
        }

        Gate::authorize('report', $casino);

        $validated = $request->validate([
            'reason' => 'required|string|in:wrong_info,spam,licensing,other',
            'details' => 'nullable|string|max:2000',
        ]);

        $userId = $request->user()->id;
        if (CasinoReport::where('casino_id', $casino->id)->where('user_id', $userId)->exists()) {
            return back()->with('error', 'You have already reported this listing.');
        }

        CasinoReport::create([
            'casino_id' => $casino->id,
            'user_id' => $userId,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thank you — your report was submitted.');
    }
}
