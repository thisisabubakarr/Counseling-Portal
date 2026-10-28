@extends('layouts.app')

@section('title', 'News & Events')

@section('content')
    <div class="page-header">
        <h2>News &amp; Events</h2>
    </div>

    <form method="POST" action="{{ route('counselor.news.store') }}" class="home-section">
        @csrf
        <div class="form-group">
            <label for="title">Title <span class="required">*</span></label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="body">Details <span class="required">*</span></label>
            <textarea id="body" name="body" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="event_date">Event Date <span class="optional-tag">(optional)</span></label>
            <input type="date" id="event_date" name="event_date">
        </div>
        <label class="checkbox-row"><input type="checkbox" name="notify_students" value="1" checked> Notify all students</label>
        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:0.75rem">Publish</button>
    </form>

    <div class="news-list">
        @forelse ($news as $item)
            <div class="news-card">
                <div class="news-card-header">
                    <h3>{{ $item->title }}</h3>
                    <form method="POST" action="{{ route('counselor.news.destroy', $item) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
                <p>{{ $item->body }}</p>
                <div class="news-card-meta">
                    @if ($item->event_date)<span>Event: {{ $item->event_date->format('M j, Y') }}</span>@endif
                    <span>By {{ $item->counselor_name }}</span>
                </div>
            </div>
        @empty
            <p class="empty-state">No news published yet.</p>
        @endforelse
    </div>

    {{ $news->links() }}
@endsection
