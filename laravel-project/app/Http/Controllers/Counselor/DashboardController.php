<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\StudentReferral;
use App\Models\SupportGroupRequest;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $counselor = Auth::guard('counselor')->user();

        $pendingAppointments = Appointment::where('counselor_id', $counselor->id)
            ->where('status', 'pending')
            ->orderBy('date')
            ->get();

        $pendingSupportGroups = SupportGroupRequest::where('status', 'pending')->latest()->get();

        $pendingStudentReferrals = StudentReferral::where('status', 'pending')->latest()->get();

        $upcomingTasks = Task::where(function ($q) use ($counselor) {
            $q->where('counselor_id', $counselor->id)->orWhereNull('counselor_id');
        })
            ->where('status', 'pending')
            ->orderBy('date')
            ->limit(5)
            ->get();

        return view('counselor.dashboard', [
            'counselor' => $counselor,
            'pendingAppointments' => $pendingAppointments,
            'pendingSupportGroups' => $pendingSupportGroups,
            'pendingStudentReferrals' => $pendingStudentReferrals,
            'upcomingTasks' => $upcomingTasks,
        ]);
    }
}
