@extends('layouts.app')

@section('title', 'Support Group Requests')

@section('content')
    <div class="page-header">
        <h2>My Support Group Requests</h2>
        <a href="{{ route('faculty.support-group.create') }}" class="btn btn-primary btn-sm">New Request</a>
    </div>

    <div class="notifications-list">
        @forelse ($requests as $request)
            <div class="notification-item">
                <div class="notification-title">{{ $request->request_code }} <span class="status-badge status-{{ $request->status }}">{{ ucfirst($request->status) }}</span></div>
                <div class="notification-message">{{ $request->reason }}</div>
                @if ($request->topics)
                    <p class="muted">Topics: {{ implode(', ', $request->topics) }}</p>
                @endif
                @if ($request->notes)
                    <p class="muted">Counselor notes: {{ $request->notes }}</p>
                @endif
            </div>
        @empty
            <p class="empty-state">You haven't requested a support group yet.</p>
        @endforelse
    </div>
@endsection
