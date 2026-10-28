@extends('layouts.app')

@section('title', 'Student Referrals')

@section('content')
    <div class="page-header">
        <h2>Student Referrals</h2>
    </div>
    <p class="muted">Students referred for counseling by faculty or staff members who noticed they may be struggling.</p>

    <div class="notifications-list" style="margin-top:1rem">
        @forelse ($referrals as $referral)
            <div class="notification-item">
                <div class="notification-title">
                    {{ $referral->student_name }} ({{ $referral->student_roll_number }}) — <span class="status-badge status-{{ $referral->status }}">{{ ucfirst($referral->status) }}</span>
                </div>
                <p class="muted">
                    Referred by <strong>{{ $referral->referred_by_name }}</strong>
                    ({{ $referral->referred_by_designation }}@if($referral->referred_by_department), {{ $referral->referred_by_department }}@endif)
                    — a faculty/staff referral, not a self-request.
                </p>
                @if ($referral->student)
                    <p class="muted"><a href="{{ route('counselor.students.show', $referral->student) }}">View student profile &rarr;</a></p>
                @else
                    <p class="muted">No matching student record found in this system yet.</p>
                @endif
                <div class="notification-message">{{ $referral->reason }}</div>
                @if ($referral->counselor_notes)
                    <p class="muted"><strong>Counselor notes:</strong> {{ $referral->counselor_notes }}</p>
                @endif

                <form method="POST" action="{{ route('counselor.student-referrals.update', $referral) }}" style="margin-top:0.6rem">
                    @csrf
                    <div class="form-group">
                        <textarea name="counselor_notes" rows="2" placeholder="Notes (optional)">{{ $referral->counselor_notes }}</textarea>
                    </div>
                    <button type="submit" name="status" value="pending" class="btn btn-secondary btn-sm">Pending</button>
                    <button type="submit" name="status" value="reviewed" class="btn btn-primary btn-sm">Reviewed</button>
                    <button type="submit" name="status" value="contacted" class="btn btn-success btn-sm">Contacted</button>
                    <button type="submit" name="status" value="closed" class="btn btn-danger btn-sm">Closed</button>
                </form>
            </div>
        @empty
            <p class="empty-state">No student referrals yet.</p>
        @endforelse
    </div>

    {{ $referrals->links() }}
@endsection
