<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Policy;
use App\Models\SessionLog;
use App\Support\CodeGenerator;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * The consent/drugs/appointments documents used to be hardcoded here.
     * They're now real `policies` rows (seeded by migration, editable by
     * counselors) so every document in the folder — standard or
     * counselor-added — comes from the same source.
     */
    public function policy()
    {
        $documents = Policy::orderedForDisplay()->get()->mapWithKeys(fn (Policy $policy) => [
            $policy->slug => [
                'title' => $policy->title,
                'description' => $policy->description ?? 'Policy',
            ],
        ]);

        return view('student.policy.index', ['documents' => $documents]);
    }

    public function policyShow(string $document)
    {
        $policy = Policy::where('slug', $document)->firstOrFail();

        return view('student.policy.show', [
            'student' => Auth::guard('student')->user(),
            'document' => $document,
            'title' => $policy->title,
            'policy' => $policy,
        ]);
    }

    public function respondNextSession(Request $request)
    {
        $student = Auth::guard('student')->user();

        $data = $request->validate(['accepted' => ['required', 'boolean']]);

        if ($student->next_session_date_status !== 'pending' || ! $student->suggested_next_session_date) {
            throw ValidationException::withMessages(['accepted' => 'No pending next session suggestion.']);
        }

        if ($data['accepted']) {
            $student->update([
                'next_session_date' => $student->suggested_next_session_date,
                'next_session_date_status' => 'accepted',
            ]);
        } else {
            $student->update(['next_session_date_status' => 'declined']);
        }

        Notifier::toCounselor(
            $student->suggestedByCounselor,
            $data['accepted'] ? 'Next session date accepted' : 'Next session date declined',
            $data['accepted']
                ? "Student {$student->roll_number} accepted the suggested next session date ({$student->suggested_next_session_date->toDateString()})."
                : "Student {$student->roll_number} declined the suggested next session date ({$student->suggested_next_session_date->toDateString()}).",
            $data['accepted'] ? 'success' : 'info'
        );

        return back()->with('status', 'Response recorded.');
    }

    public function storeAttachment(Request $request)
    {
        $student = Auth::guard('student')->user();

        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $path = $data['file']->store('attachments', 'public');

        $attachment = Attachment::create([
            'attachment_code' => CodeGenerator::make('ATT'),
            'student_id' => $student->id,
            'file_name' => $data['file']->getClientOriginalName(),
            'mime_type' => $data['file']->getClientMimeType(),
            'url' => Storage::url($path),
            'uploaded_at' => now(),
        ]);

        return back()->with('status', "Uploaded {$attachment->file_name}.");
    }

    public function meetings()
    {
        $student = Auth::guard('student')->user();
        $sessions = SessionLog::where('student_id', $student->id)->orderByDesc('date')->get();

        return view('student.meetings', ['sessions' => $sessions]);
    }
}
