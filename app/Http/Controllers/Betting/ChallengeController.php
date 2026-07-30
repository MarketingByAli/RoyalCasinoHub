<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\BettingEvent;
use App\Betting\Models\Market;
use App\Betting\Services\MarketMatchingService;
use App\Betting\Services\MarketService;
use App\Betting\Services\PlayWalletService;
use App\Betting\Services\SettlementService;
use App\Http\Controllers\Controller;
use App\Models\User;
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
                    ->orWhere('challenger_id', $user->id)
                    ->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id));
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
            'visibility' => 'nullable|in:private_invite,public',
            'participant_cap' => 'nullable|integer|min:2|max:'.config('betting.max_participant_cap', 20),
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
                ? __('betting.challenge_created_open')
                : __('betting.challenge_submitted_review'));
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

    public function accept(Request $request, Market $market, MarketService $marketService, MarketMatchingService $matching)
    {
        $validated = $request->validate([
            'invite_token' => 'nullable|string|max:64',
            'outcome' => 'nullable|string|max:100',
            'proposed_stake_amount' => 'nullable|numeric|min:1|max:'.config('betting.max_stake_per_market'),
        ]);

        $inviteToken = $validated['invite_token']
            ?? session('betting.invite_tokens.'.$market->id);

        if (is_string($inviteToken) && $inviteToken !== '') {
            session()->put('betting.invite_tokens.'.$market->id, $inviteToken);
        }

        $this->authorize('accept', $market);

        try {
            if ((int) $market->participant_cap > 2 || isset($validated['proposed_stake_amount']) || isset($validated['outcome'])) {
                $outcome = $validated['outcome'] ?? $market->challengerOutcome();
                if (! $outcome) {
                    throw new \RuntimeException('Invalid outcome selection.');
                }
                $proposed = isset($validated['proposed_stake_amount']) ? (float) $validated['proposed_stake_amount'] : null;
                $matching->join($market, auth()->user(), $outcome, $inviteToken, $proposed);
            } else {
                $marketService->acceptChallenge($market, auth()->user(), $inviteToken);
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('betting.challenges.show', $market)->with('success', __('betting.challenge_accepted'));
    }

    public function join(Request $request, Market $market, MarketMatchingService $matching)
    {
        $validated = $request->validate([
            'invite_token' => 'nullable|string|max:64',
            'outcome' => 'required|string|max:100',
            'proposed_stake_amount' => 'nullable|numeric|min:1|max:'.config('betting.max_stake_per_market'),
        ]);

        $inviteToken = $validated['invite_token'] ?? session('betting.invite_tokens.'.$market->id);
        $this->authorize('accept', $market);

        try {
            $matching->join(
                $market,
                auth()->user(),
                $validated['outcome'],
                $inviteToken,
                isset($validated['proposed_stake_amount']) ? (float) $validated['proposed_stake_amount'] : null
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('betting.challenges.show', $market)->with('success', __('betting.challenge_joined'));
    }

    public function withdraw(Market $market, MarketMatchingService $matching)
    {
        try {
            $matching->withdraw($market, auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('betting.withdrawn'));
    }

    public function acceptCounter(Request $request, Market $market, User $user, MarketMatchingService $matching)
    {
        $this->authorize('cancel', $market);

        try {
            $matching->acceptCounterOffer($market, auth()->user(), $user);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('betting.counter_accepted'));
    }

    public function rejectCounter(Request $request, Market $market, User $user, MarketMatchingService $matching)
    {
        $this->authorize('cancel', $market);

        try {
            $matching->rejectCounterOffer($market, auth()->user(), $user);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('betting.counter_rejected'));
    }

    public function decline(Market $market, MarketService $marketService)
    {
        $this->authorize('cancel', $market);

        try {
            $marketService->declineChallenge($market, auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('betting.challenges.index')->with('success', __('betting.challenge_declined'));
    }

    public function cancel(Market $market, MarketService $marketService)
    {
        $this->authorize('cancel', $market);

        try {
            $marketService->cancelBeforeMatch($market, auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('betting.challenges.index')->with('success', __('betting.challenge_cancelled'));
    }
}
