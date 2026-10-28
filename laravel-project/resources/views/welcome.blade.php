@extends('layouts.guest')

@section('title', 'Welcome')

@section('content')
    <h2>BNU Counseling Portal</h2>
    <p class="muted">This portal is accessed through the BNU CMS. Log in there — students land on their dashboard automatically, and counselors reach theirs via the Counselor tab in the CMS sidebar.</p>

    @if (app()->environment('local'))
        <p class="auth-switch" style="margin-top:1.5rem">
            No CMS to log in to yet — <a href="{{ route('dev.sso.index') }}">simulate the CMS handoff</a> for local development.
        </p>
    @endif
@endsection
