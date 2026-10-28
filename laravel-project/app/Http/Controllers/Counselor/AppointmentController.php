<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentAccepted;
use App\Mail\AppointmentRejected;
use App\Mail\AppointmentRescheduled as AppointmentRescheduledMail;
use App\Models\Appointment;
use App\Models\AppointmentReschedule;
use App\Models\SessionLog;
use App\Support\CodeGenerator;
use App\Support\Notifier;
use App\Support\SlotService;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $counselor = Auth::guard('counselor')->user();

        $appointments = Appointment::where('counselor_id', $counselor->id)
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('date')
            ->orderBy('slot')
            ->paginate(20);

        return view('counselor.appointments.index', ['appointments' => $appointments]);
    }

    public function show(Appointment $appointment)
    {
        $this->authorizeCounselor($appointment);

        return view('counselor.appointments.show', ['appointment' => $appointment->load('rescheduleHistory', 'student', 'facultyStaff')]);
    }

    public function availableSlots(Request $request)
    {
        $counselor = Auth::guard('counselor')->user();

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'exclude_appointment_id' => ['nullable', 'integer'],
        ]);

        return response()->json(SlotService::getAvailableSlots($data['date'], $counselor->id, $data['exclude_appointment_id'] ?? null));
    }

    public function accept(Appointment $appointment)
    {
        $counselor = $this->authorizeCounselor($appointment);

        if (! SlotService::isWeekday($appointment->date->toDateString())) {
            throw ValidationException::withMessages(['status' => 'Appointments are only available Monday through Friday.']);
        }

        if (SlotService::isSlotTaken($counselor->id, $appointment->date->toDateString(), $appointment->slot, $appointment->id)) {
            throw ValidationException::withMessages(['status' => 'This slot is already booked by another appointment.']);
        }

        $appointment->update(['status' => 'accepted']);

        // Clinical safety fields (suicide_intent/suicide_alert) only exist on
        // Student, per CLAUDE.md's hard constraint — faculty/staff have no
        // equivalent column; the appointment's own suicidal_thoughts flag is
        // still visible to the counselor regardless of owner type.
        if ($appointment->student_id && $appointment->suicidal_thoughts) {
            $appointment->student->update(['suicide_intent' => true, 'suicide_alert' => true]);
        }

        SessionLog::create([
            'session_code' => CodeGenerator::make('SES'),
            'student_id' => $appointment->student_id,
            'faculty_staff_id' => $appointment->faculty_staff_id,
            'date' => $appointment->date,
            'time' => explode(' - ', $appointment->slot)[0],
            'notes' => '',
            'status' => 'scheduled',
        ]);

        $this->notifyOwner($appointment, 'Appointment accepted', "Your appointment ({$appointment->request_code}) was accepted for {$appointment->date->toDateString()} at {$appointment->slot} with {$counselor->name}.", 'success');
        $this->mailOwner($appointment, new AppointmentAccepted($appointment));

        return back()->with('status', 'Appointment accepted.');
    }

    public function reject(Request $request, Appointment $appointment)
    {
        $counselor = $this->authorizeCounselor($appointment);

        $appointment->update(['status' => 'rejected']);

        $this->notifyOwner($appointment, 'Appointment rejected', "Your appointment request ({$appointment->request_code}) was not approved. Please book again or contact the Counseling Center.", 'warning');
        $this->mailOwner($appointment, new AppointmentRejected($appointment));

        return back()->with('status', 'Appointment rejected.');
    }

    /**
     * Counselor proposes a new date/slot — does NOT change the appointment
     * yet. The student/faculty-staff owner must approve it first. The
     * original slot stays booked; the proposed one is merely held.
     */
    public function requestReschedule(Request $request, Appointment $appointment)
    {
        $counselor = $this->authorizeCounselor($appointment);

        if (in_array($appointment->status, SlotService::RESCHEDULE_PENDING_STATUSES, true)) {
            throw ValidationException::withMessages(['date' => 'A reschedule is already pending approval for this appointment.']);
        }

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'slot' => ['required', 'string'],
        ]);

        if (! SlotService::isWithin14Days($data['date'])) {
            throw ValidationException::withMessages(['date' => 'Appointments can only be booked up to 14 days in advance.']);
        }

        if (! SlotService::isWeekday($data['date'])) {
            throw ValidationException::withMessages(['date' => 'Appointments are only available Monday through Friday.']);
        }

        if (SlotService::isSlotTaken($counselor->id, $data['date'], $data['slot'], $appointment->id)) {
            throw ValidationException::withMessages(['slot' => 'Selected slot is no longer available.']);
        }

        $appointment->update([
            'pending_reschedule_date' => $data['date'],
            'pending_reschedule_slot' => $data['slot'],
            'reschedule_requested_by' => 'counselor',
            'status_before_reschedule' => $appointment->status,
            'status' => 'reschedule_pending_user',
        ]);

        $this->notifyOwner($appointment, 'Counselor proposed a new time', "{$counselor->name} proposed moving appointment ({$appointment->request_code}) to {$data['date']} at {$data['slot']}. Please accept or decline.", 'info');

        return back()->with('status', 'Reschedule proposed — pending the other party\'s approval.');
    }

    /**
     * Approve/decline a reschedule the STUDENT/FACULTY-STAFF owner
     * proposed (status reschedule_pending_counselor).
     */
    public function respondToReschedule(Request $request, Appointment $appointment)
    {
        $counselor = $this->authorizeCounselor($appointment);

        if ($appointment->status !== 'reschedule_pending_counselor') {
            throw ValidationException::withMessages(['status' => 'There is no reschedule request awaiting your response.']);
        }

        $data = $request->validate(['accepted' => ['required', 'boolean']]);

        if ($data['accepted']) {
            if (SlotService::isSlotTaken($counselor->id, $appointment->pending_reschedule_date->toDateString(), $appointment->pending_reschedule_slot, $appointment->id)) {
                throw ValidationException::withMessages(['status' => 'That slot was just taken by another appointment. Ask them to propose another time.']);
            }

            $fromDate = $appointment->date->toDateString();
            $fromSlot = $appointment->slot;
            $toDate = $appointment->pending_reschedule_date->toDateString();
            $toSlot = $appointment->pending_reschedule_slot;

            AppointmentReschedule::create([
                'appointment_id' => $appointment->id,
                'from_date' => $fromDate,
                'from_slot' => $fromSlot,
                'to_date' => $toDate,
                'to_slot' => $toSlot,
                'by' => $appointment->reschedule_requested_by,
                'at' => now(),
            ]);

            $appointment->update([
                'date' => $toDate,
                'slot' => $toSlot,
                'status' => 'rescheduled',
                'pending_reschedule_date' => null,
                'pending_reschedule_slot' => null,
                'reschedule_requested_by' => null,
                'status_before_reschedule' => null,
            ]);

            $this->notifyOwner($appointment, 'Reschedule confirmed', "Appointment ({$appointment->request_code}) is now confirmed for {$toDate} at {$toSlot}.", 'success');
            $this->mailOwner($appointment, new AppointmentRescheduledMail($appointment, $fromDate, $fromSlot));

            return back()->with('status', 'Reschedule confirmed.');
        }

        $appointment->update([
            'status' => $appointment->status_before_reschedule ?? 'accepted',
            'pending_reschedule_date' => null,
            'pending_reschedule_slot' => null,
            'reschedule_requested_by' => null,
            'status_before_reschedule' => null,
        ]);

        $this->notifyOwner($appointment, 'Reschedule declined', "Your request to reschedule appointment ({$appointment->request_code}) was declined. The original appointment stands.", 'warning');

        return back()->with('status', 'Reschedule declined — original appointment stands.');
    }

    public function updateNotes(Request $request, Appointment $appointment)
    {
        $this->authorizeCounselor($appointment);

        $data = $request->validate(['counsellor_notes' => ['nullable', 'string']]);
        $appointment->update($data);

        return back()->with('status', 'Notes saved.');
    }

    private function authorizeCounselor(Appointment $appointment)
    {
        $counselor = Auth::guard('counselor')->user();
        abort_unless($appointment->counselor_id === $counselor->id, 403);

        return $counselor;
    }

    private function notifyOwner(Appointment $appointment, string $title, string $message, string $type): void
    {
        if ($appointment->faculty_staff_id) {
            Notifier::toFacultyStaff($appointment->facultyStaff, $title, $message, $type);
        } else {
            Notifier::toStudent($appointment->student, $title, $message, $type);
        }
    }

    private function mailOwner(Appointment $appointment, Mailable $mailable): void
    {
        if ($appointment->faculty_staff_id) {
            Notifier::mailFacultyStaff($appointment->facultyStaff, $mailable);
        } else {
            Notifier::mailStudent($mailable);
        }
    }
}
