@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
    <div class="page-header">
        <h2>Welcome, {{ $student->name }}</h2>
    </div>

    @if ($student->next_session_date_status === 'pending')
        <div class="next-session-banner">
            <div class="next-session-banner-text">
                <strong>Your counsellor suggested a new next-session date</strong>
                <p>{{ optional($student->suggested_next_session_date)->format('D, M j, Y') }}</p>
            </div>
            <div class="next-session-banner-actions">
                <form method="POST" action="{{ route('student.next-session.respond') }}">
                    @csrf
                    <input type="hidden" name="accepted" value="1">
                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                </form>
                <form method="POST" action="{{ route('student.next-session.respond') }}">
                    @csrf
                    <input type="hidden" name="accepted" value="0">
                    <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                </form>
            </div>
        </div>
    @endif

    <div class="home-dashboard">
        <div class="home-section">
            <h3>Active Appointment</h3>
            <div class="widget-content">
                @if ($activeAppointment)
                    <div class="appointment-item">
                        <p><strong>{{ $activeAppointment->counselor_name }}</strong> — {{ $activeAppointment->date->format('D, M j, Y') }} at {{ $activeAppointment->slot }}</p>
                        <p><span class="status-badge status-{{ $activeAppointment->status }}">{{ $activeAppointment->statusLabel() }}</span></p>
                        <p style="margin-top:0.6rem"><a href="{{ route('student.appointments.show', $activeAppointment) }}" class="btn btn-primary btn-sm">View Details</a></p>
                    </div>
                @else
                    <p class="empty-state">No active appointment. <a href="{{ route('student.appointments.create') }}">Book one now</a>.</p>
                @endif
            </div>
        </div>

        <div class="home-section">
            <h3>Recent Notifications</h3>
            <div class="widget-content">
                @forelse ($recentNotifications as $notification)
                    <div class="activity-item">
                        <strong>{{ $notification->title }}</strong>
                        <p>{{ $notification->message }}</p>
                    </div>
                @empty
                    <p class="empty-state">No notifications yet.</p>
                @endforelse
            </div>
        </div>

        <div class="home-section full-width">
            <h3>Quick Actions</h3>
            <div class="action-cards">
                <a href="{{ route('student.appointments.create') }}" class="action-card">
                    <h3><i class="fa-solid fa-calendar-plus"></i> Book Appointment</h3>
                    <p>Schedule a session with a counselor within the next 14 days.</p>
                </a>
                <a href="{{ route('student.support-group.create') }}" class="action-card">
                    <h3><i class="fa-solid fa-people-group"></i> Support Group</h3>
                    <p>Request to join a peer support group.</p>
                </a>
                <a href="{{ route('student.meetings') }}" class="action-card">
                    <h3><i class="fa-solid fa-clock-rotate-left"></i> My Meetings</h3>
                    <p>Review your past and scheduled sessions.</p>
                </a>
            </div>
        </div>
    </div>
@endsection
