@extends('layouts.app')

@section('title', 'Refer a Student')

@section('content')
    <div class="page-header">
        <h2>Refer a Student</h2>
    </div>
    <p class="muted">If you've noticed a student who may be experiencing personal, academic, emotional, or other difficulties, refer them to a counselor here. The referral will clearly show it came from you, a faculty/staff member — not from the student.</p>

    <form method="POST" action="{{ route('faculty.referrals.store') }}" class="home-section">
        @csrf
        <div class="detail-grid">
            <div class="form-group">
                <label for="student_name">Student Name <span class="required">*</span></label>
                <input type="text" id="student_name" name="student_name" value="{{ old('student_name') }}" required>
                @error('student_name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="student_roll_number">Student Roll Number <span class="required">*</span></label>
                <input type="text" id="student_roll_number" name="student_roll_number" value="{{ old('student_roll_number') }}" required>
                @error('student_roll_number')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="reason">Reason for Referral <span class="required">*</span></label>
            <textarea id="reason" name="reason" rows="4" placeholder="Briefly describe what you've noticed and why you think this student could benefit from counseling." required>{{ old('reason') }}</textarea>
            @error('reason')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:0.75rem">Submit Referral</button>
    </form>

    <div class="page-header" style="margin-top:1.5rem">
        <h2>My Referrals</h2>
    </div>
    <div class="notifications-list">
        @forelse ($referrals as $referral)
            <div class="notification-item">
                <div class="notification-title">{{ $referral->student_name }} ({{ $referral->student_roll_number }}) <span class="status-badge status-{{ $referral->status }}">{{ ucfirst($referral->status) }}</span></div>
                <div class="notification-message">{{ $referral->reason }}</div>
                @if ($referral->counselor_notes)
                    <p class="muted">Counselor notes: {{ $referral->counselor_notes }}</p>
                @endif
            </div>
        @empty
            <p class="empty-state">You haven't referred any students yet.</p>
        @endforelse
    </div>
@endsection
