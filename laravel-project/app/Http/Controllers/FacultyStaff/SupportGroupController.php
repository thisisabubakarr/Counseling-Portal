<?php

namespace App\Http\Controllers\FacultyStaff;

use App\Http\Controllers\Controller;
use App\Models\SupportGroupRequest;
use App\Support\CodeGenerator;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportGroupController extends Controller
{
    public function create()
    {
        return view('faculty.support-group.create');
    }

    public function index()
    {
        $facultyStaff = Auth::guard('faculty_staff')->user();
        $requests = SupportGroupRequest::where('faculty_staff_id', $facultyStaff->id)->latest()->get();

        return view('faculty.support-group.index', ['requests' => $requests]);
    }

    public function store(Request $request)
    {
        $facultyStaff = Auth::guard('faculty_staff')->user();

        $data = $request->validate([
            'reason' => ['required', 'string'],
            'topics' => ['nullable', 'array'],
        ]);

        $groupRequest = SupportGroupRequest::create([
            'request_code' => CodeGenerator::make('SG'),
            'faculty_staff_id' => $facultyStaff->id,
            'faculty_staff_name' => $facultyStaff->name,
            'employee_id' => $facultyStaff->employee_id,
            'reason' => $data['reason'],
            'topics' => $data['topics'] ?? [],
            'status' => 'pending',
        ]);

        Notifier::toFacultyStaff($facultyStaff, 'Support group request submitted', "{$facultyStaff->name} requested a support group ({$groupRequest->request_code}). Pending counselor review.", 'info');
        Notifier::toCounselor(null, 'Support group request submitted', "{$facultyStaff->name} ({$facultyStaff->employee_id}) requested a support group ({$groupRequest->request_code}).", 'info');

        return redirect()->route('faculty.support-group.index')->with('status', 'Support group request submitted.');
    }
}
