<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentSubmitted;
use App\Models\Appointment;
use App\Models\AppointmentReschedule;
use App\Models\Counselor;
use App\Models\Policy;
use App\Support\CodeGenerator;
use App\Support\Notifier;
use App\Support\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function create()
    {
        $student = Auth::guard('student')->user();
        $counselors = Counselor::where('active', true)->orderBy('name')->get();

        $hasActive = Appointment::where('student_id', $student->id)
            ->whereIn('status', SlotService::BOOKED_STATUSES)
            ->exists();

        return view('student.appointments.create', [
            'student' => $student,
            'counselors' => $counselors,
            'hasActive' => $hasActive,
            'consentPolicy' => Policy::where('slug', 'consent')->first(),
        ]);
    }

    public function availableSlots(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'counselor_id' => ['nullable', 'integer'],
            'exclude_appointment_id' => ['nullable', 'integer'],
        ]);

        return response()->json(
            SlotService::getAvailableSlots($data['date'], $data['counselor_id'] ?? null, $data['exclude_appointment_id'] ?? null)
        );
    }

    public function store(Request $request)
    {
        $student = Auth::guard('student')->user();

        $data = $request->validate([
            'consent' => ['accepted'],
            'counselor_id' => ['required', 'exists:counselors,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'slot' => ['required', 'string'],
            'suicidal_thoughts' => ['nullable', 'boolean'],
            'mental_health_questions' => ['nullable', 'string'],
            'presenting_concerns' => ['nullable', 'array'],
            'support_group_topics' => ['nullable', 'array'],
            'emergency_contact_name' => ['required', 'string'],
            'emergency_contact_phone' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'consent.accepted' => 'You must read and agree to the consent agreement before booking.',
        ]);

        if (! SlotService::isWithin14Days($data['date'])) {
            throw ValidationException::withMessages(['date' => 'Appointments can only be booked up to 14 days in advance.']);
        }

        if (! SlotService::isWeekday($data['date'])) {
            throw ValidationException::withMessages(['date' => 'Appointments are only available Monday through Friday.']);
        }

        $existingActive = Appointment::where('student_id', $student->id)
            ->whereIn('status', SlotService::BOOKED_STATUSES)
            ->exists();
        if ($existingActive) {
            throw ValidationException::withMessages(['date' => 'You already have an active appointment. You cannot book another session until it is completed, rejected, or cancelled.']);
        }

        if (SlotService::isSlotTaken((int) $data['counselor_id'], $data['date'], $data['slot'])) {
            throw ValidationException::withMessages(['slot' => 'Selected slot is no longer available.']);
        }

        $available = collect(SlotService::getAvailableSlots($data['date'], (int) $data['counselor_id']));
        if (! $available->pluck('label')->contains($data['slot'])) {
            throw ValidationException::withMessages(['slot' => 'Selected slot is not available.']);
        }

        $counselor = Counselor::findOrFail($data['counselor_id']);

        $appointment = Appointment::create([
            'request_code' => CodeGenerator::make('REQ'),
            'student_id' => $student->id,
            'student_name' => $student->name,
            'roll_number' => $student->roll_number,
            'counselor_id' => $counselor->id,
            'counselor_name' => $counselor->name,
            'date' => $data['date'],
            'slot' => $data['slot'],
            'suicidal_thoughts' => (bool) ($data['suicidal_thoughts'] ?? false),
            'mental_health_questions' => $data['mental_health_questions'] ?? null,
            'presenting_concerns' => $data['presenting_concerns'] ?? [],
            'support_group_topics' => $data['support_group_topics'] ?? [],
            'emergency_contact_name' => $data['emergency_contact_name'],
            'emergency_contact_phone' => $data['emergency_contact_phone'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        if (! empty($data['presenting_concerns'])) {
            $student->update(['presenting_concerns' => $data['presenting_concerns']]);
        }

        Notifier::toStudent($student, 'Appointment request submitted', "Your appointment request ({$appointment->request_code}) is pending for {$appointment->date->toDateString()} at {$appointment->slot} with {$counselor->name}.", 'success');
        Notifier::toCounselor($counselor, 'Appointment request submitted', "{$student->name} ({$student->roll_number}) requested an appointment ({$appointment->request_code}) for {$appointment->date->toDateString()} at {$appointment->slot}.", 'success');
        Notifier::mailStudent(new AppointmentSubmitted($appointment));
        Notifier::mailCounselor($counselor, new AppointmentSubmitted($appointment));

        return redirect()->route('student.appointments.show', $appointment)->with('status', 'Appointment request submitted.');
    }

    public function show(Appointment $appointment)
    {
        $this->authorizeStudent($appointment);

        return view('student.appointments.show', ['appointment' => $appointment->load('rescheduleHistory')]);
    }

    public function cancel(Appointment $appointment)
    {
        $this->authorizeStudent($appointment);

        if (! in_array($appointment->status, SlotService::BOOKED_STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'This appointment can no longer be cancelled.']);
        }

        $appointment->update([
            'status' => 'cancelled',
            'pending_reschedule_date' => null,
            'pending_reschedule_slot' => null,
            'reschedule_requested_by' => null,
            'status_before_reschedule' => null,
        ]);

        return redirect()->route('student.dashboard')->with('status', 'Appointment cancelled.');
    }

    /**
     * Propose a new date/slot — this does NOT change the appointment yet.
     * The counselor must approve it first (see CmsSsoController-style
     * separation: nothing here is "immediate"). The original slot stays
     * booked; the proposed one is merely held (see SlotService).
     */
    public function requestReschedule(Request $request, Appointment $appointment)
    {
        $student = $this->authorizeStudent($appointment);

        if (in_array($appointment->status, SlotService::RESCHEDULE_PENDING_STATUSES, true)) {
            throw ValidationException::withMessages(['date' => 'A reschedule is already pending approval for this appointment.']);
        }

        if (! in_array($appointment->status, ['pending', 'accepted', 'rescheduled'], true)) {
            throw ValidationException::withMessages(['date' => 'This appointment can no longer be rescheduled.']);
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

        $studentReschedules = $appointment->rescheduleHistory()->where('by', 'student')->count();
        if ($studentReschedules >= 1) {
            throw ValidationException::withMessages(['date' => 'You can only reschedule an appointment once.']);
        }

        if (SlotService::isSlotTaken($appointment->counselor_id, $data['date'], $data['slot'], $appointment->id)) {
            throw ValidationException::withMessages(['slot' => 'Selected slot is no longer available.']);
        }

        $appointment->update([
            'pending_reschedule_date' => $data['date'],
            'pending_reschedule_slot' => $data['slot'],
            'reschedule_requested_by' => 'student',
            'status_before_reschedule' => $appointment->status,
            'status' => 'reschedule_pending_counselor',
        ]);

        Notifier::toStudent($student, 'Reschedule request sent', "Your request to move appointment ({$appointment->request_code}) to {$data['date']} at {$data['slot']} is pending counselor approval.", 'info');
        Notifier::toCounselor($appointment->counselor, 'Reschedule requested', "{$student->name} ({$student->roll_number}) requested to move appointment ({$appointment->request_code}) to {$data['date']} at {$data['slot']}.", 'info');

        return redirect()->route('student.appointments.show', $appointment)->with('status', 'Reschedule request sent — pending counselor approval.');
    }

    /**
     * Approve/decline a reschedule the COUNSELOR proposed (status
     * reschedule_pending_user). The student's own proposals are approved
     * by the counselor, not here.
     */
    public function respondToReschedule(Request $request, Appointment $appointment)
    {
        $student = $this->authorizeStudent($appointment);

        if ($appointment->status !== 'reschedule_pending_user') {
            throw ValidationException::withMessages(['status' => 'There is no counselor-proposed reschedule awaiting your response.']);
        }

        $data = $request->validate(['accepted' => ['required', 'boolean']]);

        if ($data['accepted']) {
            if (SlotService::isSlotTaken($appointment->counselor_id, $appointment->pending_reschedule_date->toDateString(), $appointment->pending_reschedule_slot, $appointment->id)) {
                throw ValidationException::withMessages(['status' => 'That slot was just taken by someone else. Please ask the counselor to propose another time.']);
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
                'by' => 'counsellor',
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

            Notifier::toStudent($student, 'Reschedule confirmed', "Appointment ({$appointment->request_code}) is now confirmed for {$toDate} at {$toSlot}.", 'success');
            Notifier::toCounselor($appointment->counselor, 'Reschedule confirmed', "{$student->name} accepted the new time for appointment ({$appointment->request_code}): {$toDate} at {$toSlot}.", 'success');
            Notifier::mailStudent(new \App\Mail\AppointmentRescheduled($appointment, $fromDate, $fromSlot));

            return redirect()->route('student.appointments.show', $appointment)->with('status', 'Reschedule confirmed.');
        }

        $appointment->update([
            'status' => $appointment->status_before_reschedule ?? 'accepted',
            'pending_reschedule_date' => null,
            'pending_reschedule_slot' => null,
            'reschedule_requested_by' => null,
            'status_before_reschedule' => null,
        ]);

        Notifier::toCounselor($appointment->counselor, 'Reschedule declined', "{$student->name} declined the proposed new time for appointment ({$appointment->request_code}). The original time stands.", 'warning');

        return redirect()->route('student.appointments.show', $appointment)->with('status', 'Reschedule declined — your original appointment stands.');
    }

    private function authorizeStudent(Appointment $appointment)
    {
        $student = Auth::guard('student')->user();
        abort_unless($appointment->student_id === $student->id, 403);

        return $student;
    }
}
