@extends('layouts.app')

@section('title', $title)

@section('content')
    <a href="{{ route('student.policy') }}" class="btn btn-secondary" style="margin-bottom:1rem"><i class="fa-solid fa-arrow-left"></i> Back</a>

    <div class="home-section">
        <div class="consent-document">
            <h4>{{ $policy->title }}</h4>
            <div class="policy-body">{!! nl2br(e($policy->body)) !!}</div>

            @if ($document === 'consent')
                @include('student.partials.consent-acknowledgement')
            @endif
        </div>
    </div>
@endsection
