<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\FacultyStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Mirrors StudentController's directory pattern, scoped to faculty/staff
 * who have booked with this counselor. No update/suggestNextSession here —
 * faculty/staff have no clinical columns to manage (see CLAUDE.md hard
 * constraint, which only applies to Student).
 */
class FacultyStaffController extends Controller
{
    public function index(Request $request)
    {
        $counselor = Auth::guard('counselor')->user();

        $facultyStaff = FacultyStaff::whereHas('appointments', function ($q) use ($counselor) {
            $q->where('counselor_id', $counselor->id);
        })
            ->when($request->query('q'), function ($q, $term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('employee_id', 'like', "%{$term}%");
            })->orderBy('name')->paginate(20);

        return view('counselor.faculty-staff.index', ['facultyStaff' => $facultyStaff]);
    }

    public function show(FacultyStaff $facultyStaff)
    {
        $this->authorizeCounselor($facultyStaff);

        $facultyStaff->load(['appointments', 'sessionLogs', 'attachments']);

        return view('counselor.faculty-staff.show', ['facultyStaff' => $facultyStaff]);
    }

    private function authorizeCounselor(FacultyStaff $facultyStaff): void
    {
        $counselor = Auth::guard('counselor')->user();

        abort_unless(
            $facultyStaff->appointments()->where('counselor_id', $counselor->id)->exists(),
            403
        );
    }
}
