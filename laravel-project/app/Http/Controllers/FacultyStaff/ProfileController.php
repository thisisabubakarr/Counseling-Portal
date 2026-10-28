<?php

namespace App\Http\Controllers\FacultyStaff;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Policy;
use App\Models\SessionLog;
use App\Support\CodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function policy()
    {
        $documents = Policy::orderedForDisplay()->get()->mapWithKeys(fn (Policy $policy) => [
            $policy->slug => [
                'title' => $policy->title,
                'description' => $policy->description ?? 'Policy',
            ],
        ]);

        return view('faculty.policy.index', ['documents' => $documents]);
    }

    public function policyShow(string $document)
    {
        $policy = Policy::where('slug', $document)->firstOrFail();

        return view('faculty.policy.show', [
            'facultyStaff' => Auth::guard('faculty_staff')->user(),
            'document' => $document,
            'title' => $policy->title,
            'policy' => $policy,
        ]);
    }

    public function storeAttachment(Request $request)
    {
        $facultyStaff = Auth::guard('faculty_staff')->user();

        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $path = $data['file']->store('attachments', 'public');

        $attachment = Attachment::create([
            'attachment_code' => CodeGenerator::make('ATT'),
            'faculty_staff_id' => $facultyStaff->id,
            'file_name' => $data['file']->getClientOriginalName(),
            'mime_type' => $data['file']->getClientMimeType(),
            'url' => Storage::url($path),
            'uploaded_at' => now(),
        ]);

        return back()->with('status', "Uploaded {$attachment->file_name}.");
    }

    public function meetings()
    {
        $facultyStaff = Auth::guard('faculty_staff')->user();
        $sessions = SessionLog::where('faculty_staff_id', $facultyStaff->id)->orderByDesc('date')->get();

        return view('faculty.meetings', ['sessions' => $sessions]);
    }
}
