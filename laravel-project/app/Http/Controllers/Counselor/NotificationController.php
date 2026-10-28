<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('to_role', 'counsellor')->latest()->paginate(20);

        return view('counselor.notifications.index', ['notifications' => $notifications]);
    }

    public function markRead(Notification $notification)
    {
        abort_unless($notification->to_role === 'counsellor', 403);

        $notification->update(['read_at' => now()]);

        return back();
    }
}
