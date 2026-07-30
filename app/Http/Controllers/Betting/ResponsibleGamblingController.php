<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\RgLimit;
use App\Betting\Services\ResponsibleGamblingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResponsibleGamblingController extends Controller
{
    public function edit(Request $request)
    {
        $limits = RgLimit::firstOrNew(['user_id' => $request->user()->id]);
        $rg = app(ResponsibleGamblingService::class);

        return view('betting.rg.edit', [
            'limits' => $limits,
            'coolOff' => $rg->activeAction($request->user(), 'cool_off'),
            'selfExclusion' => $rg->activeAction($request->user(), 'self_exclusion'),
        ]);
    }

    public function updateLimits(Request $request, ResponsibleGamblingService $rg)
    {
        $validated = $request->validate([
            'daily_stake_limit' => 'nullable|numeric|min:1|max:'.config('betting.max_open_liability_per_user'),
            'weekly_stake_limit' => 'nullable|numeric|min:1|max:'.config('betting.max_open_liability_per_user'),
        ]);

        $rg->upsertLimits(
            $request->user(),
            isset($validated['daily_stake_limit']) ? (float) $validated['daily_stake_limit'] : null,
            isset($validated['weekly_stake_limit']) ? (float) $validated['weekly_stake_limit'] : null,
        );

        return back()->with('success', __('rg.limits_saved'));
    }

    public function coolOff(Request $request, ResponsibleGamblingService $rg)
    {
        $validated = $request->validate([
            'hours' => 'required|integer|in:24,72,168',
        ]);

        $rg->startCoolOff($request->user(), (int) $validated['hours']);

        return back()->with('success', __('rg.cool_off_started'));
    }

    public function selfExclude(Request $request, ResponsibleGamblingService $rg)
    {
        $validated = $request->validate([
            'days' => 'nullable|integer|min:7|max:365',
        ]);

        $ends = isset($validated['days']) ? now()->addDays((int) $validated['days']) : null;
        $rg->startSelfExclusion($request->user(), $ends);

        return redirect()->route('betting.dashboard')->with('success', __('rg.self_excluded'));
    }
}
