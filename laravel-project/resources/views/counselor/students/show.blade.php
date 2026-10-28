@extends('layouts.app')

@section('title', $student->name)

@section('content')
    <div class="page-header">
        <h2>{{ $student->name }} ({{ $student->roll_number }})</h2>
    </div>

    <div class="profile-detail">
        <dl class="detail-grid">
            <div><dt>Department</dt><dd>{{ $student->department }}</dd></div>
            <div><dt>Semester</dt><dd>{{ $student->semester }}</dd></div>
            <div><dt>Phone</dt><dd>{{ $student->phone }}</dd></div>
            <div><dt>Date of Birth</dt><dd>{{ optional($student->dob)->format('M j, Y') }}</dd></div>
            <div><dt>Session Status</dt><dd>{{ $student->session_status }}</dd></div>
            <div><dt>Nature of Consent</dt><dd>{{ $student->nature_of_consent }}</dd></div>
        </dl>

        @if ($student->presenting_concerns)
            <p style="margin-top:1rem"><strong>Presenting Concerns:</strong> {{ implode(', ', $student->presenting_concerns) }}</p>
        @endif

        <form method="POST" action="{{ route('counselor.students.update', $student) }}" style="margin-top:1.25rem">
            @csrf
            <div class="detail-grid">
                <div class="form-group">
                    <label for="session_status">Session Status</label>
                    <select id="session_status" name="session_status">
                        @foreach (['Ongoing', 'Rescheduled', 'Completed', 'Discontinued'] as $status)
                            <option value="{{ $status }}" {{ $student->session_status === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="nature_of_consent">Nature of Consent</label>
                    <input type="text" id="nature_of_consent" name="nature_of_consent" value="{{ $student->nature_of_consent }}">
                </div>
            </div>
            <div class="risk-flags">
                <label><input type="checkbox" name="suicide_intent" value="1" {{ $student->suicide_intent ? 'checked' : '' }}> Suicide intent flagged</label>
                <label><input type="checkbox" name="suicide_alert" value="1" {{ $student->suicide_alert ? 'checked' : '' }}> Active safety alert</label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:0.75rem">Save Profile</button>
        </form>

        <form method="POST" action="{{ route('counselor.students.next-session.suggest', $student) }}" style="margin-top:1.25rem">
            @csrf
            <div class="form-group">
                <label for="next_date">Suggest Next Session Date</label>
                <input type="date" id="next_date" name="date" required>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">Suggest Date</button>
        </form>
    </div>

    <div class="home-dashboard" style="margin-top:1.25rem">
        <div class="home-section">
            <h3>Appointments</h3>
            <div class="widget-content">
                @forelse ($student->appointments as $appointment)
                    <div class="appointment-item">
                        {{ $appointment->date->format('M j, Y') }} at {{ $appointment->slot }} — <span class="status-badge status-{{ $appointment->status }}">{{ $appointment->statusLabel() }}</span>
                    </div>
                @empty
                    <p class="empty-state">No appointments yet.</p>
                @endforelse
            </div>
        </div>

        <div class="home-section">
            <h3>Session Logs</h3>
            <div class="widget-content">
                @forelse ($student->sessionLogs as $log)
                    <div class="appointment-item">{{ $log->date->format('M j, Y') }} {{ $log->time }} — {{ ucfirst($log->status) }}</div>
                @empty
                    <p class="empty-state">No session logs yet.</p>
                @endforelse
            </div>
        </div>

        <div class="home-section full-width">
            <h3>Attachments</h3>
            <ul class="attachment-list">
                @forelse ($student->attachments as $attachment)
                    <li class="attachment-item"><a href="{{ $attachment->url }}" target="_blank">{{ $attachment->file_name }}</a></li>
                @empty
                    <li class="empty-state">No attachments uploaded.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
