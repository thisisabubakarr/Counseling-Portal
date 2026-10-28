@extends('layouts.app')

@section('title', 'Appointment ' . $appointment->request_code)

@section('content')
    <div class="page-header">
        <h2>Appointment {{ $appointment->request_code }}</h2>
        <span class="status-badge status-{{ $appointment->status }}">{{ $appointment->statusLabel() }}</span>
    </div>

    <div class="profile-detail">
        <dl class="detail-grid">
            @if ($appointment->faculty_staff_id)
                <div><dt>Faculty/Staff</dt><dd>{{ $appointment->faculty_staff_name }} ({{ $appointment->employee_id }})</dd></div>
            @else
                <div><dt>Student</dt><dd>{{ $appointment->student_name }} ({{ $appointment->roll_number }})</dd></div>
            @endif
            <div><dt>Date</dt><dd>{{ $appointment->date->format('D, M j, Y') }}</dd></div>
            <div><dt>Time</dt><dd>{{ $appointment->slot }}</dd></div>
            <div><dt>Emergency Contact</dt><dd>{{ $appointment->emergency_contact_name }} ({{ $appointment->emergency_contact_phone }})</dd></div>
        </dl>

        @if ($appointment->suicidal_thoughts)
            <p class="status-badge suicide-alert-label" style="margin-top:0.75rem"><i class="fa-solid fa-triangle-exclamation"></i> Flagged: Suicidal thoughts reported</p>
        @endif

        @if ($appointment->presenting_concerns)
            <p style="margin-top:1rem"><strong>Presenting Concerns:</strong> {{ implode(', ', $appointment->presenting_concerns) }}</p>
        @endif

        @if ($appointment->mental_health_questions)
            <p style="margin-top:0.5rem"><strong>Additional info:</strong> {{ $appointment->mental_health_questions }}</p>
        @endif

        @if ($appointment->rescheduleHistory->isNotEmpty())
            <div style="margin-top:1rem">
                <strong>Reschedule History</strong>
                <ul class="reschedule-history-list">
                    @foreach ($appointment->rescheduleHistory as $history)
                        <li>{{ $history->from_date->toDateString() }} {{ $history->from_slot }} → {{ $history->to_date->toDateString() }} {{ $history->to_slot }} (by {{ $history->by }})</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($appointment->status === 'reschedule_pending_counselor')
            <div class="consent-confirmation-box" style="margin-top:1rem">
                <h5>{{ $appointment->owner()->name }} Proposed a New Time</h5>
                <p>{{ $appointment->pending_reschedule_date->format('D, M j, Y') }} at {{ $appointment->pending_reschedule_slot }} (currently {{ $appointment->date->format('D, M j, Y') }} at {{ $appointment->slot }})</p>
                <form method="POST" action="{{ route('counselor.appointments.reschedule.respond', $appointment) }}" style="display:inline">
                    @csrf
                    <input type="hidden" name="accepted" value="1">
                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                </form>
                <form method="POST" action="{{ route('counselor.appointments.reschedule.respond', $appointment) }}" style="display:inline">
                    @csrf
                    <input type="hidden" name="accepted" value="0">
                    <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                </form>
            </div>
        @elseif ($appointment->status === 'reschedule_pending_user')
            <div class="consent-confirmation-box" style="margin-top:1rem">
                <h5>Reschedule Pending Their Approval</h5>
                <p>You proposed {{ $appointment->pending_reschedule_date->format('D, M j, Y') }} at {{ $appointment->pending_reschedule_slot }}. The original appointment stays booked until they respond.</p>
            </div>
        @endif

        <div class="meeting-card-actions">
            @if ($appointment->status === 'pending')
                <form method="POST" action="{{ route('counselor.appointments.accept', $appointment) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                </form>
                <form method="POST" action="{{ route('counselor.appointments.reject', $appointment) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                </form>
            @endif

            @if (in_array($appointment->status, ['pending', 'accepted', 'rescheduled']))
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('rescheduleForm').classList.toggle('hidden')">Reschedule</button>
                <form id="rescheduleForm" method="POST" action="{{ route('counselor.appointments.reschedule', $appointment) }}" class="hidden" style="margin-top:1rem">
                    @csrf
                    <div class="detail-grid">
                        <div class="form-group">
                            <label for="new_date">New Date</label>
                            <input type="date" id="new_date" name="date" min="{{ now()->toDateString() }}" max="{{ now()->addDays(14)->toDateString() }}" required>
                            <p class="field-error" id="rescheduleWeekendError" style="display:none">Weekends are not available. Please choose a weekday (Mon–Fri).</p>
                        </div>
                        <div class="form-group">
                            <label>Available Slots</label>
                            <div class="slot-container" id="rescheduleSlotContainer">
                                <p class="no-slots">Select a date to see available slots.</p>
                            </div>
                        </div>
                    </div>
                    <p class="muted" style="margin-top:0.5rem">Their current slot stays booked until they approve this request.</p>
                    <button type="submit" class="btn btn-primary btn-sm">Send Reschedule Request</button>
                </form>
            @endif
        </div>

        <form method="POST" action="{{ route('counselor.appointments.notes', $appointment) }}" style="margin-top:1.25rem">
            @csrf
            <div class="form-group">
                <label for="counsellor_notes">Session Notes</label>
                <textarea id="counsellor_notes" name="counsellor_notes" rows="4" class="session-notes">{{ $appointment->counsellor_notes }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Save Notes</button>
        </form>
    </div>
@endsection

@section('scripts')
<script>
    const rescheduleDateInput = document.getElementById('new_date');
    if (rescheduleDateInput) {
        const rescheduleWeekendError = document.getElementById('rescheduleWeekendError');
        const rescheduleSlotContainer = document.getElementById('rescheduleSlotContainer');

        function isWeekend(dateStr) {
            if (!dateStr) return false;
            const [y, m, d] = dateStr.split('-').map(Number);
            const day = new Date(y, m - 1, d).getDay();
            return day === 0 || day === 6;
        }

        async function loadRescheduleSlots() {
            const date = rescheduleDateInput.value;
            if (!date) {
                rescheduleSlotContainer.innerHTML = '<p class="no-slots">Select a date to see available slots.</p>';
                return;
            }
            rescheduleSlotContainer.innerHTML = '<p class="no-slots">Loading slots...</p>';
            try {
                const res = await fetch(`{{ route('counselor.appointments.available-slots') }}?date=${date}&exclude_appointment_id={{ $appointment->id }}`);
                if (!res.ok || !res.headers.get('content-type')?.includes('application/json')) {
                    rescheduleSlotContainer.innerHTML = '<p class="no-slots">Your session may have expired. Please refresh the page and log in again.</p>';
                    return;
                }
                const slots = await res.json();
                if (!slots.length) {
                    rescheduleSlotContainer.innerHTML = '<p class="no-slots">No slots available for this date.</p>';
                    return;
                }
                rescheduleSlotContainer.innerHTML = slots.map((s, i) => `
                    <label class="slot-option">
                        <input type="radio" name="slot" value="${s.label}" ${i === 0 ? 'required' : ''}>
                        ${s.label}
                    </label>
                `).join('');
            } catch (e) {
                rescheduleSlotContainer.innerHTML = '<p class="no-slots">Could not load slots. Please refresh the page and try again.</p>';
            }
        }

        rescheduleDateInput.addEventListener('change', () => {
            if (isWeekend(rescheduleDateInput.value)) {
                rescheduleWeekendError.style.display = '';
                rescheduleDateInput.value = '';
                loadRescheduleSlots();
                return;
            }
            rescheduleWeekendError.style.display = 'none';
            loadRescheduleSlots();
        });
    }
</script>
@endsection
