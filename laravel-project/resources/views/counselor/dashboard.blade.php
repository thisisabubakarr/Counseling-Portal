@extends('layouts.app')

@section('title', 'Counselor Dashboard')

@section('content')
    <div class="counsellor-dashboard">
        <h2>Welcome, {{ $counselor->name }}</h2>

        <div class="home-dashboard">
            <div class="home-section">
                <h3>Pending Appointment Requests</h3>
                <div class="widget-content">
                    @forelse ($pendingAppointments as $appointment)
                        <div class="appointment-item">
                            @if ($appointment->faculty_staff_id)
                                <p><strong>{{ $appointment->faculty_staff_name }}</strong> ({{ $appointment->employee_id }}) <span class="status-badge">Faculty/Staff</span></p>
                            @else
                                <p><strong>{{ $appointment->student_name }}</strong> ({{ $appointment->roll_number }})</p>
                            @endif
                            <p>{{ $appointment->date->format('D, M j, Y') }} at {{ $appointment->slot }}</p>
                            <p style="margin-top:0.5rem"><a href="{{ route('counselor.appointments.show', $appointment) }}" class="btn btn-primary btn-sm">Review</a></p>
                        </div>
                    @empty
                        <p class="empty-state">No pending requests.</p>
                    @endforelse
                </div>
                <p style="margin-top:0.75rem"><a href="{{ route('counselor.appointments.index') }}" class="btn btn-secondary btn-sm">View All</a></p>
            </div>

            <div class="home-section">
                <h3>Pending Support Group Requests</h3>
                <div class="widget-content">
                    @forelse ($pendingSupportGroups as $request)
                        <div class="appointment-item">
                            @if ($request->faculty_staff_id)
                                <p><strong>{{ $request->faculty_staff_name }}</strong> ({{ $request->employee_id }}) <span class="status-badge">Faculty/Staff</span></p>
                            @else
                                <p><strong>{{ $request->student_name }}</strong> ({{ $request->roll_number }})</p>
                            @endif
                            <p>{{ $request->reason }}</p>
                        </div>
                    @empty
                        <p class="empty-state">No pending support group requests.</p>
                    @endforelse
                </div>
                <p style="margin-top:0.75rem"><a href="{{ route('counselor.support-group.index') }}" class="btn btn-secondary btn-sm">View All</a></p>
            </div>

            <div class="home-section">
                <h3>Pending Student Referrals</h3>
                <div class="widget-content">
                    @forelse ($pendingStudentReferrals as $referral)
                        <div class="appointment-item">
                            <p><strong>{{ $referral->student_name }}</strong> ({{ $referral->student_roll_number }})</p>
                            <p>Referred by {{ $referral->referred_by_name }}</p>
                        </div>
                    @empty
                        <p class="empty-state">No pending student referrals.</p>
                    @endforelse
                </div>
                <p style="margin-top:0.75rem"><a href="{{ route('counselor.student-referrals.index') }}" class="btn btn-secondary btn-sm">View All</a></p>
            </div>

            <div class="home-section full-width">
                <h3>Upcoming Tasks</h3>
                <div class="widget-content">
                    @forelse ($upcomingTasks as $task)
                        <div class="task-item">
                            <strong>{{ $task->title }}</strong> — {{ $task->date->format('M j, Y') }} {{ $task->time }}
                        </div>
                    @empty
                        <p class="empty-state">No upcoming tasks.</p>
                    @endforelse
                </div>
                <p style="margin-top:0.75rem"><a href="{{ route('counselor.tasks.index') }}" class="btn btn-secondary btn-sm">Manage Tasks</a></p>
            </div>
        </div>
    </div>
@endsection
