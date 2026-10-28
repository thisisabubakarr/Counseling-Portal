@extends('layouts.app')

@section('title', 'My Meetings')

@section('content')
    <div class="meetings-page">
        <h2><i class="fa-solid fa-clock-rotate-left"></i> My Meetings</h2>
        <p class="muted">Your scheduled and past counseling sessions.</p>

        <div class="meetings-list" style="margin-top:1rem">
            @forelse ($sessions as $session)
                <div class="meeting-card">
                    <div class="meeting-card-header">
                        <strong>{{ $session->date->format('D, M j, Y') }} at {{ $session->time }}</strong>
                        <span class="status-badge status-{{ $session->status }}">{{ ucfirst($session->status) }}</span>
                    </div>
                    @if ($session->notes)
                        <p>{{ $session->notes }}</p>
                    @endif
                </div>
            @empty
                <p class="empty-state">No sessions on record yet.</p>
            @endforelse
        </div>

        <div class="meetings-section">
            <h3>Upload Attachment</h3>
            <form method="POST" action="{{ route('faculty.attachments.store') }}" enctype="multipart/form-data" class="attachment-upload-row">
                @csrf
                <input type="file" name="file" required>
                <button type="submit" class="btn btn-primary btn-sm">Upload</button>
            </form>
        </div>
    </div>
@endsection
