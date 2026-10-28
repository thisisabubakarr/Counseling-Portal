<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\StudentReferral;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentReferralController extends Controller
{
    public function index()
    {
        $referrals = StudentReferral::latest()->paginate(20);

        return view('counselor.student-referrals.index', ['referrals' => $referrals]);
    }

    public function update(Request $request, StudentReferral $studentReferral)
    {
        $counselor = Auth::guard('counselor')->user();

        $data = $request->validate([
            'status' => ['required', 'in:pending,reviewed,contacted,closed'],
            'counselor_notes' => ['nullable', 'string'],
        ]);

        $studentReferral->update([
            'status' => $data['status'],
            'counselor_notes' => $data['counselor_notes'] ?? $studentReferral->counselor_notes,
            'reviewed_by_counselor_id' => $counselor->id,
        ]);

        Notifier::toFacultyStaff(
            $studentReferral->facultyStaff,
            'Student referral updated',
            "Your referral for {$studentReferral->student_name} ({$studentReferral->referral_code}) status is now: ".ucfirst($data['status']).'.',
            $data['status'] === 'closed' ? 'warning' : 'info'
        );

        return back()->with('status', 'Referral updated.');
    }
}
