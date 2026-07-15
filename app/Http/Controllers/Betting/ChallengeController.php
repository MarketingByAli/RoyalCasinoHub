<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\BettingEvent;
use App\Betting\Models\Market;
use App\Betting\Services\MarketService;
use App\Betting\Services\PlayWalletService;
use App\Betting\Services\SettlementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $markets = Market::query()
            ->with(['event', 'creator.bettingProfile', 'challenger.bettingProfile'])
            ->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                    ->orWhere('challenger_id', $user->id);
            })
            ->latest()
            ->paginate(20);

        return view('betting.challenges.index', compact('markets'));
    }

    public function create()
    {
        $events = BettingEvent::approvedForBetting()->where('start_at', '>', now())->orderBy('start_at')->get();

        return view('betting.challenges.create', compact('events'));
    }

    public function store(Request $request, MarketService $marketService)
    {
        $validated = $request->validate([
            'betting_event_id' => 'required|exists:betting_events,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'format' => 'required|in:yes_no,team_vs_team',
            'team_a' => 'required_if:format,team_vs_team|nullable|string|max:100',
            'team_b' => 'required_if:format,team_vs_team|nullable|string|max:100',
            'creator_outcome' => 'required|string|max:100',
            'stake_amount' => 'required|numeric|min:1|max:'.config('betting.max_stake_per_market'),
        ]);

        $event = BettingEvent::approvedForBetting()
            ->where('start_at', '>', now())
            ->findOrFail($validated['betting_event_id']);

        try {
            $market = $marketService->createDraft($request->user(), $event, $validated);
            $market = $marketService->submitForReview($market, $request->user());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('betting.challenges.show', $market)
            ->with('success', $market->status->value === 'open'
                ? 'Challenge created! Share your invite link.'
                : 'Challenge submitted for review.');
    }

    public function show(Market $market, PlayWalletService $walletService, SettlementService $settlementService)
    {
        if ($token = request('invite_token')) {
            session()->put('betting.invite_tokens.'.$market->id, $token);
        }

        $market = $settlementService->ensureDisputeWindowFinalized($market);

        $this->authorize('view', $market);

        $market->load(['event', 'creator.bettingProfile', 'challenger.bettingProfile', 'currentVersion', 'participants.user.bettingProfile']);

        $wallet = auth()->check() ? $walletService->getOrCreateWallet(auth()->user()) : null;
        $inviteToken = session('betting.invite_tokens.'.$market->id);

        return view('betting.challenges.show', compact('market', 'wallet', 'inviteToken'));
    }

    public function accept(Request $request, Market $market, MarketService $marketService)
    {
        $validated = $request->validate([
            'invite_token' => 'nullable|string|max:64',
        ]);

        $inviteToken = $validated['invite_token']
            ?? session('betting.invite_tokens.'.$market->id);

        if (is_string($inviteToken) && $inviteToken !== '') {
            session()->put('betting.invite_tokens.'.$market->id, $inviteToken);
            $request->merge(['invite_token' => $inviteToken]);
        }

        $this->authorize('accept', $market);

        try {
            $marketService->acceptChallenge($market, auth()->user(), $inviteToken);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('betting.challenges.show', $market)->with('success', 'Challenge accepted! Stakes are locked.');
    }

    public function decline(Market $market, MarketService $marketService)
    {
        $this->authorize('cancel', $market);

        try {
            $marketService->declineChallenge($market, auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('betting.challenges.index')->with('success', 'Challenge declined.');
    }

    public function cancel(Market $market, MarketService $marketService)
    {
        $this->authorize('cancel', $market);

        try {
            $marketService->cancelBeforeMatch($market, auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('betting.challenges.index')->with('success', 'Challenge cancelled.');
    }
}
