<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $counselor = Auth::guard('counselor')->user();

        $students = Student::whereHas('appointments', function ($q) use ($counselor) {
            $q->where('counselor_id', $counselor->id);
        })
            ->when($request->query('q'), function ($q, $term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('roll_number', 'like', "%{$term}%");
            })->orderBy('name')->paginate(20);

        return view('counselor.students.index', ['students' => $students]);
    }

    public function show(Student $student)
    {
        $this->authorizeCounselor($student);

        $student->load(['appointments', 'sessionLogs', 'attachments', 'supportGroupRequests']);

        return view('counselor.students.show', ['student' => $student]);
    }

    public function update(Request $request, Student $student)
    {
        $this->authorizeCounselor($student);

        $data = $request->validate([
            'session_status' => ['nullable', 'string'],
            'suicide_intent' => ['nullable', 'boolean'],
            'suicide_alert' => ['nullable', 'boolean'],
            'nature_of_consent' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'semester' => ['nullable', 'string'],
            'cgpa' => ['nullable', 'numeric'],
        ]);

        $student->update($data);

        return back()->with('status', 'Student profile updated.');
    }

    public function suggestNextSession(Request $request, Student $student)
    {
        $counselor = $this->authorizeCounselor($student);

        $data = $request->validate(['date' => ['required', 'date_format:Y-m-d']]);

        $student->update([
            'suggested_next_session_date' => $data['date'],
            'next_session_date_status' => 'pending',
            'suggested_by_counselor_id' => $counselor->id,
        ]);

        Notifier::toStudent($student, 'Next session date suggested', "Your counsellor suggested {$data['date']} as your next session date. Please accept or decline in the student portal.", 'info');

        return back()->with('status', 'Next session date suggested.');
    }

    private function authorizeCounselor(Student $student)
    {
        $counselor = Auth::guard('counselor')->user();

        abort_unless(
            $student->appointments()->where('counselor_id', $counselor->id)->exists(),
            403
        );

        return $counselor;
    }
}
