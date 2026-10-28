<?php

namespace App\Http\Controllers\Student;

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
        return view('student.support-group.create');
    }

    public function index()
    {
        $student = Auth::guard('student')->user();
        $requests = SupportGroupRequest::where('student_id', $student->id)->latest()->get();

        return view('student.support-group.index', ['requests' => $requests]);
    }

    public function store(Request $request)
    {
        $student = Auth::guard('student')->user();

        $data = $request->validate([
            'reason' => ['required', 'string'],
            'topics' => ['nullable', 'array'],
        ]);

        $groupRequest = SupportGroupRequest::create([
            'request_code' => CodeGenerator::make('SG'),
            'student_id' => $student->id,
            'student_name' => $student->name,
            'roll_number' => $student->roll_number,
            'reason' => $data['reason'],
            'topics' => $data['topics'] ?? [],
            'status' => 'pending',
        ]);

        Notifier::toStudent($student, 'Support group request submitted', "{$student->name} requested a support group ({$groupRequest->request_code}). Pending counselor review.", 'info');
        Notifier::toCounselor(null, 'Support group request submitted', "{$student->name} ({$student->roll_number}) requested a support group ({$groupRequest->request_code}).", 'info');

        return redirect()->route('student.support-group.index')->with('status', 'Support group request submitted.');
    }
}
