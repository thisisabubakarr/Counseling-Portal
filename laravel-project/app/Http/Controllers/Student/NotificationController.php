<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        $notifications = Notification::where('to_role', 'student')
            ->where(function ($query) use ($student) {
                $query->where('to_student_id', $student->id)
                    ->orWhereNull('to_student_id');
            })
            ->latest()
            ->paginate(20);

        return view('student.notifications.index', ['notifications' => $notifications]);
    }

    public function markRead(Notification $notification)
    {
        $student = Auth::guard('student')->user();
        abort_unless($notification->to_role === 'student' && ($notification->to_student_id === null || $notification->to_student_id === $student->id), 403);

        $notification->update(['read_at' => now()]);

        return back();
    }
}
