@extends('layouts.app')

@section('title', 'Support Group Requests')

@section('content')
    <div class="page-header">
        <h2>Support Group Requests</h2>
    </div>

    <div class="notifications-list">
        @forelse ($requests as $request)
            <div class="notification-item">
                <div class="notification-title">
                    @if ($request->faculty_staff_id)
                        {{ $request->faculty_staff_name }} ({{ $request->employee_id }}) <span class="status-badge">Faculty/Staff</span>
                    @else
                        {{ $request->student_name }} ({{ $request->roll_number }})
                    @endif
                    — <span class="status-badge status-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                </div>
                <div class="notification-message">{{ $request->reason }}</div>
                @if ($request->topics)
                    <p class="muted">Topics: {{ implode(', ', $request->topics) }}</p>
                @endif

                @if ($request->status === 'pending')
                    <form method="POST" action="{{ route('counselor.support-group.update', $request) }}" style="margin-top:0.6rem">
                        @csrf
                        <div class="form-group">
                            <textarea name="notes" rows="2" placeholder="Notes (optional)"></textarea>
                        </div>
                        <button type="submit" name="status" value="accepted" class="btn btn-success btn-sm">Accept</button>
                        <button type="submit" name="status" value="rejected" class="btn btn-danger btn-sm">Reject</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="empty-state">No support group requests yet.</p>
        @endforelse
    </div>

    {{ $requests->links() }}
@endsection
