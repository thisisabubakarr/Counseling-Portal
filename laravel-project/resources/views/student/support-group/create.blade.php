@extends('layouts.app')

@section('title', 'Request Support Group')

@section('content')
    <div class="page-header">
        <h2>Request a Support Group</h2>
        <a href="{{ route('student.support-group.index') }}" class="btn btn-secondary btn-sm">My Requests</a>
    </div>

    <form method="POST" action="{{ route('student.support-group.store') }}">
        @csrf
        <div class="home-section">
            <div class="form-group">
                <label for="reason">Why are you interested in a support group? <span class="required">*</span></label>
                <textarea id="reason" name="reason" rows="4" required>{{ old('reason') }}</textarea>
            </div>
            <div class="form-group">
                <label>Topics of Interest</label>
                <div class="concern-group">
                    @foreach ([
                        'Academic stress and burnout',
                        'Navigating relationships',
                        'Managing anxiety and emotional overwhelm',
                        'Exploring grief',
                        'Boundaries, people-pleasing, and emotional guilt',
                        'Struggling with addiction',
                        'Struggling to connect with others',
                        'Meaning in Life',
                    ] as $topic)
                        <label class="checkbox-row">
                            <input type="checkbox" name="topics[]" value="{{ $topic }}">
                            {{ $topic }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="profile-actions">
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
    </form>
@endsection
