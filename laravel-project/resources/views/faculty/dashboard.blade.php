@extends('layouts.app')

@section('title', 'Faculty & Staff Dashboard')

@section('content')
    <div class="page-header">
        <h2>Welcome, {{ $facultyStaff->name }}</h2>
    </div>
    <p class="muted">{{ $facultyStaff->designation }}@if($facultyStaff->department) — {{ $facultyStaff->department }} @endif ({{ $facultyStaff->employee_id }})</p>

    <div class="home-dashboard">
        <div class="home-section">
            <h3>Active Appointment</h3>
            <div class="widget-content">
                @if ($activeAppointment)
                    <div class="appointment-item">
                        <p><strong>{{ $activeAppointment->counselor_name }}</strong> — {{ $activeAppointment->date->format('D, M j, Y') }} at {{ $activeAppointment->slot }}</p>
                        <p><span class="status-badge status-{{ $activeAppointment->status }}">{{ $activeAppointment->statusLabel() }}</span></p>
                        <p style="margin-top:0.6rem"><a href="{{ route('faculty.appointments.show', $activeAppointment) }}" class="btn btn-primary btn-sm">View Details</a></p>
                    </div>
                @else
                    <p class="empty-state">No active appointment. <a href="{{ route('faculty.appointments.create') }}">Book one now</a>.</p>
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
                <a href="{{ route('faculty.referrals.index') }}" class="action-card">
                    <h3><i class="fa-solid fa-share-from-square"></i> Student Referral</h3>
                    <p>Refer a student you're concerned about to a counselor.</p>
                </a>
                <a href="{{ route('faculty.appointments.create') }}" class="action-card">
                    <h3><i class="fa-solid fa-calendar-plus"></i> Book Appointment</h3>
                    <p>Schedule a session with a counselor within the next 14 days.</p>
                </a>
                <a href="{{ route('faculty.support-group.create') }}" class="action-card">
                    <h3><i class="fa-solid fa-people-group"></i> Support Group</h3>
                    <p>Request to join a peer support group.</p>
                </a>
                <a href="{{ route('faculty.meetings') }}" class="action-card">
                    <h3><i class="fa-solid fa-clock-rotate-left"></i> My Meetings</h3>
                    <p>Review your past and scheduled sessions.</p>
                </a>
            </div>
        </div>
    </div>
@endsection
