<?php

namespace App\Http\Controllers\Betting;

use App\Betting\Models\BettingNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = BettingNotification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(30);

        return view('betting.notifications.index', compact('notifications'));
    }

    public function unreadCount(Request $request)
    {
        $count = BettingNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, BettingNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        return back();
    }

    public function markAllRead(Request $request)
    {
        BettingNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', __('betting.notifications_marked_read'));
    }
}
