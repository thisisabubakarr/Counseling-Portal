<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\SupportGroupRequest;
use App\Support\Notifier;
use Illuminate\Http\Request;

class SupportGroupController extends Controller
{
    public function index()
    {
        $requests = SupportGroupRequest::latest()->paginate(20);

        return view('counselor.support-group.index', ['requests' => $requests]);
    }

    public function update(Request $request, SupportGroupRequest $supportGroupRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,accepted,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $supportGroupRequest->update($data);

        if (in_array($data['status'], ['accepted', 'rejected'], true)) {
            $title = $data['status'] === 'accepted' ? 'Support group request accepted' : 'Support group request declined';
            $message = $data['status'] === 'accepted'
                ? "Your support group request ({$supportGroupRequest->request_code}) was accepted."
                : "Your support group request ({$supportGroupRequest->request_code}) was not approved.";
            $type = $data['status'] === 'accepted' ? 'success' : 'warning';

            if ($supportGroupRequest->faculty_staff_id) {
                Notifier::toFacultyStaff($supportGroupRequest->facultyStaff, $title, $message, $type);
            } else {
                Notifier::toStudent($supportGroupRequest->student, $title, $message, $type);
            }
        }

        return back()->with('status', 'Support group request updated.');
    }
}
