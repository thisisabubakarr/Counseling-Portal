<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use App\Support\SlotService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        $activeAppointment = Appointment::where('student_id', $student->id)
            ->whereIn('status', SlotService::BOOKED_STATUSES)
            ->orderBy('date')
            ->first();

        $recentNotifications = Notification::where('to_role', 'student')
            ->where(function ($query) use ($student) {
                $query->where('to_student_id', $student->id)->orWhereNull('to_student_id');
            })
            ->latest()
            ->limit(5)
            ->get();

        return view('student.dashboard', [
            'student' => $student,
            'activeAppointment' => $activeAppointment,
            'recentNotifications' => $recentNotifications,
        ]);
    }
}
