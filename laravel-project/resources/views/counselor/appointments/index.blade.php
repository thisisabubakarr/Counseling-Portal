@extends('layouts.app')

@section('title', 'Appointments')

@section('content')
    <div class="page-header">
        <h2>Appointments</h2>
    </div>

    <div class="student-tabs">
        @foreach ([
            '' => 'All',
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'reschedule_pending_counselor' => 'Reschedule Requested',
            'reschedule_pending_user' => 'Awaiting Their Approval',
            'rescheduled' => 'Rescheduled',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
        ] as $value => $label)
            <a href="{{ route('counselor.appointments.index', $value ? ['status' => $value] : []) }}" class="student-tab {{ request('status', '') === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Requested By</th>
                    <th>Date</th>
                    <th>Slot</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $appointment)
                    <tr>
                        <td>{{ $appointment->request_code }}</td>
                        <td>
                            @if ($appointment->faculty_staff_id)
                                {{ $appointment->faculty_staff_name }} ({{ $appointment->employee_id }}) <span class="status-badge">Faculty/Staff</span>
                            @else
                                {{ $appointment->student_name }} ({{ $appointment->roll_number }})
                            @endif
                        </td>
                        <td>{{ $appointment->date->format('M j, Y') }}</td>
                        <td>{{ $appointment->slot }}</td>
                        <td><span class="status-badge status-{{ $appointment->status }}">{{ $appointment->statusLabel() }}</span></td>
                        <td><a href="{{ route('counselor.appointments.show', $appointment) }}" class="btn btn-primary btn-sm">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No appointments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $appointments->links() }}
@endsection
