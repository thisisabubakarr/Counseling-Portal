@extends('layouts.app')

@section('title', 'Policies')

@section('content')
    <a href="{{ route('student.dashboard') }}" class="btn btn-secondary" style="margin-bottom:1rem"><i class="fa-solid fa-arrow-left"></i> Back</a>

    <div class="page-header">
        <h2><i class="fa-solid fa-folder-open"></i> Policies</h2>
    </div>
    <p class="muted">Policies of the Center for Counseling and Psychological Well-being (CCPW) at BNU. Open a folder to view the document.</p>

    <div class="action-cards" style="margin-top:1.5rem">
        @foreach ($documents as $slug => $doc)
            <a href="{{ route('student.policy.show', $slug) }}" class="action-card policy-card">
                <i class="fa-solid fa-folder policy-folder-icon"></i>
                <h3>{{ $doc['title'] }}</h3>
                <p>{{ $doc['description'] }}</p>
            </a>
        @endforeach
    </div>
@endsection
