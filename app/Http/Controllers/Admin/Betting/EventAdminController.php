<?php

namespace App\Http\Controllers\Admin\Betting;

use App\Betting\Models\BettingEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventAdminController extends Controller
{
    public function index()
    {
        $events = BettingEvent::withCount('markets')->latest()->paginate(25);

        return view('admin.betting.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.betting.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:64',
            'organiser' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_at' => 'required|date|after:now',
            'completes_at' => 'nullable|date|after:start_at',
            'betting_close_at' => 'nullable|date|before:start_at',
            'settlement_source' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(6);
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'scheduled';

        BettingEvent::create($validated);

        return redirect()->route('admin.betting.events.index')->with('success', 'Event created.');
    }

    public function edit(BettingEvent $event)
    {
        return view('admin.betting.events.edit', compact('event'));
    }

    public function update(Request $request, BettingEvent $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:64',
            'organiser' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_at' => 'required|date',
            'completes_at' => 'nullable|date',
            'betting_close_at' => 'nullable|date',
            'settlement_source' => 'nullable|string|max:255',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        $event->update($validated);

        return redirect()->route('admin.betting.events.index')->with('success', 'Event updated.');
    }

    public function publishResult(Request $request, BettingEvent $event, \App\Betting\Services\SettlementService $settlementService)
    {
        $validated = $request->validate([
            'winning_outcome' => 'required|string|max:100',
        ]);

        try {
            $settlementService->publishEventResult($event, $validated['winning_outcome'], auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Result published for all matched markets on this event.');
    }
}
