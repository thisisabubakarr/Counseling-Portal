<?php

namespace App\Http\Controllers\FacultyStaff;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $facultyStaff = Auth::guard('faculty_staff')->user();

        $notifications = Notification::where('to_role', 'faculty_staff')
            ->where(function ($query) use ($facultyStaff) {
                $query->where('to_faculty_staff_id', $facultyStaff->id)
                    ->orWhereNull('to_faculty_staff_id');
            })
            ->latest()
            ->paginate(20);

        return view('faculty.notifications.index', ['notifications' => $notifications]);
    }

    public function markRead(Notification $notification)
    {
        $facultyStaff = Auth::guard('faculty_staff')->user();
        abort_unless($notification->to_role === 'faculty_staff' && ($notification->to_faculty_staff_id === null || $notification->to_faculty_staff_id === $facultyStaff->id), 403);

        $notification->update(['read_at' => now()]);

        return back();
    }
}
