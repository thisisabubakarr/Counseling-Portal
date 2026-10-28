@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="page-header">
        <h2>Notifications</h2>
    </div>

    <div class="notifications-list">
        @forelse ($notifications as $notification)
            <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                <div class="notification-title">{{ $notification->title }}</div>
                <div class="notification-message">{{ $notification->message }}</div>
                <div class="notification-meta">
                    <span class="muted">{{ $notification->created_at->diffForHumans() }}</span>
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('student.notifications.read', $notification) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Mark read</button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <p class="empty-state">No notifications yet.</p>
        @endforelse
    </div>

    {{ $notifications->links() }}
@endsection
