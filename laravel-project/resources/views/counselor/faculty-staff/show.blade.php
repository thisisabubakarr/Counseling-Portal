@extends('layouts.app')

@section('title', $facultyStaff->name)

@section('content')
    <div class="page-header">
        <h2>{{ $facultyStaff->name }} ({{ $facultyStaff->employee_id }})</h2>
    </div>

    <div class="profile-detail">
        <dl class="detail-grid">
            <div><dt>Designation</dt><dd>{{ $facultyStaff->designation }}</dd></div>
            <div><dt>Department</dt><dd>{{ $facultyStaff->department }}</dd></div>
            <div><dt>Email</dt><dd>{{ $facultyStaff->email }}</dd></div>
            <div><dt>Phone</dt><dd>{{ $facultyStaff->phone }}</dd></div>
        </dl>
    </div>

    <div class="home-dashboard" style="margin-top:1.25rem">
        <div class="home-section">
            <h3>Appointments</h3>
            <div class="widget-content">
                @forelse ($facultyStaff->appointments as $appointment)
                    <div class="appointment-item">
                        <a href="{{ route('counselor.appointments.show', $appointment) }}">{{ $appointment->date->format('M j, Y') }} at {{ $appointment->slot }}</a> — <span class="status-badge status-{{ $appointment->status }}">{{ $appointment->statusLabel() }}</span>
                    </div>
                @empty
                    <p class="empty-state">No appointments yet.</p>
                @endforelse
            </div>
        </div>

        <div class="home-section">
            <h3>Meetings</h3>
            <div class="widget-content">
                @forelse ($facultyStaff->sessionLogs as $log)
                    <div class="appointment-item">{{ $log->date->format('M j, Y') }} {{ $log->time }} — {{ ucfirst($log->status) }}</div>
                @empty
                    <p class="empty-state">No meetings on record yet.</p>
                @endforelse
            </div>
        </div>

        <div class="home-section full-width">
            <h3>Attachments</h3>
            <ul class="attachment-list">
                @forelse ($facultyStaff->attachments as $attachment)
                    <li class="attachment-item"><a href="{{ $attachment->url }}" target="_blank">{{ $attachment->file_name }}</a></li>
                @empty
                    <li class="empty-state">No attachments uploaded.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
