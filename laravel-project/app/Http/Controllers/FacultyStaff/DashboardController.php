<?php

namespace App\Http\Controllers\FacultyStaff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use App\Support\SlotService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $facultyStaff = Auth::guard('faculty_staff')->user();

        $activeAppointment = Appointment::where('faculty_staff_id', $facultyStaff->id)
            ->whereIn('status', SlotService::BOOKED_STATUSES)
            ->orderBy('date')
            ->first();

        $recentNotifications = Notification::where('to_role', 'faculty_staff')
            ->where(function ($query) use ($facultyStaff) {
                $query->where('to_faculty_staff_id', $facultyStaff->id)->orWhereNull('to_faculty_staff_id');
            })
            ->latest()
            ->limit(5)
            ->get();

        return view('faculty.dashboard', [
            'facultyStaff' => $facultyStaff,
            'activeAppointment' => $activeAppointment,
            'recentNotifications' => $recentNotifications,
        ]);
    }
}
