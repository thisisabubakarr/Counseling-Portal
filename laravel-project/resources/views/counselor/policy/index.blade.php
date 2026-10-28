@extends('layouts.app')

@section('title', 'Policies')

@section('content')
    <div class="page-header">
        <h2>Policies</h2>
    </div>
    <p class="muted">Policies added here appear in the students' Policy folder alongside the standard consent, drug, and appointment documents. Standard documents can be edited or deleted — deleting one (especially the consent form) removes it from the student Policy folder and the appointment booking flow.</p>

    <form method="POST" action="{{ route('counselor.policy.store') }}" class="home-section">
        @csrf
        <div class="form-group">
            <label for="title">Title <span class="required">*</span></label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Short Description <span class="optional-tag">(optional)</span></label>
            <input type="text" id="description" name="description" maxlength="255">
        </div>
        <div class="form-group">
            <label for="body">Policy Content <span class="required">*</span></label>
            <textarea id="body" name="body" rows="6" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:0.75rem">Publish Policy</button>
    </form>

    <div class="news-list">
        @forelse ($policies as $policy)
            @php
                $isStandard = in_array($policy->slug, $reservedSlugs, true);
                $confirmMessage = $isStandard
                    ? 'Delete this STANDARD document? It will disappear from the student Policy folder'.($policy->slug === 'consent' ? ", and students won't be able to give consent before booking an appointment." : '.').' This cannot be undone.'
                    : 'Delete this policy? Students will no longer be able to view it.';
            @endphp
            <div class="news-card">
                <div class="news-card-header">
                    <h3>{{ $policy->title }} @if ($isStandard)<span class="status-badge">Standard document</span>@endif</h3>
                    <form method="POST" action="{{ route('counselor.policy.destroy', $policy) }}" onsubmit="return confirm(@js($confirmMessage));">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
                @if ($policy->description)
                    <p>{{ $policy->description }}</p>
                @endif
                <div class="news-card-meta">
                    <span>By {{ $policy->counselor_name }}</span>
                    <span>Added {{ $policy->created_at->format('M j, Y') }}</span>
                </div>

                <details style="margin-top:0.75rem">
                    <summary class="btn btn-secondary btn-sm" style="display:inline-block; cursor:pointer;">Edit</summary>
                    <form method="POST" action="{{ route('counselor.policy.update', $policy) }}" style="margin-top:0.75rem">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="title-{{ $policy->id }}">Title <span class="required">*</span></label>
                            <input type="text" id="title-{{ $policy->id }}" name="title" value="{{ $policy->title }}" required>
                        </div>
                        <div class="form-group">
                            <label for="description-{{ $policy->id }}">Short Description <span class="optional-tag">(optional)</span></label>
                            <input type="text" id="description-{{ $policy->id }}" name="description" value="{{ $policy->description }}" maxlength="255">
                        </div>
                        <div class="form-group">
                            <label for="body-{{ $policy->id }}">Policy Content <span class="required">*</span></label>
                            <textarea id="body-{{ $policy->id }}" name="body" rows="10" required>{{ $policy->body }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:0.75rem">Save Changes</button>
                    </form>
                </details>
            </div>
        @empty
            <p class="empty-state">No policies published yet.</p>
        @endforelse
    </div>
@endsection
