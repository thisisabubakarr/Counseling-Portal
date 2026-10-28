<?php

namespace App\Http\Controllers\FacultyStaff;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Support\CodeGenerator;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentReferralController extends Controller
{
    public function index()
    {
        $facultyStaff = Auth::guard('faculty_staff')->user();

        $referrals = StudentReferral::where('faculty_staff_id', $facultyStaff->id)->latest()->get();

        return view('faculty.referrals.index', ['referrals' => $referrals]);
    }

    public function store(Request $request)
    {
        $facultyStaff = Auth::guard('faculty_staff')->user();

        $data = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'student_roll_number' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string'],
        ]);

        // The referring faculty/staff member may only know the student's
        // roll number, not whether they've ever logged into this system —
        // link to an existing Student row when we can find one, but never
        // require it (see migration comment on student_referrals).
        $matchedStudent = Student::where('roll_number', $data['student_roll_number'])->first();

        $referral = StudentReferral::create([
            'referral_code' => CodeGenerator::make('REF'),
            'faculty_staff_id' => $facultyStaff->id,
            'referred_by_name' => $facultyStaff->name,
            'referred_by_designation' => $facultyStaff->designation,
            'referred_by_department' => $facultyStaff->department,
            'student_id' => $matchedStudent?->id,
            'student_name' => $data['student_name'],
            'student_roll_number' => $data['student_roll_number'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        Notifier::toCounselor(null, 'Student referral submitted', "{$facultyStaff->name} ({$facultyStaff->designation}) referred student {$data['student_name']} ({$data['student_roll_number']}) for counseling ({$referral->referral_code}).", 'warning');

        return redirect()->route('faculty.referrals.index')->with('status', 'Student referral submitted.');
    }
}
