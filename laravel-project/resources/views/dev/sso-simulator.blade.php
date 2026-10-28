@extends('layouts.guest')

@section('title', 'Dev CMS SSO Simulator')

@section('content')
    <h2>Simulate CMS Login</h2>
    <p class="muted">Local dev only — stands in for the real BNU CMS handoff until it exists. Pick who you're arriving as.</p>

    <h3 style="margin-top:1.5rem">Students</h3>
    <div class="action-cards" style="margin-top:0.5rem">
        @foreach (['CMS-STU-1001' => 'Ayesha Khan', 'CMS-STU-1002' => 'Bilal Ahmed'] as $id => $name)
            <form method="POST" action="{{ route('dev.sso.mint') }}">
                @csrf
                <input type="hidden" name="role" value="student">
                <input type="hidden" name="cms_id" value="{{ $id }}">
                <button type="submit" class="action-card" style="width:100%; text-align:left; border:1px solid var(--line); background:none; cursor:pointer;">
                    <h3><i class="fa-solid fa-graduation-cap"></i> {{ $name }}</h3>
                    <p>{{ $id }}</p>
                </button>
            </form>
        @endforeach
    </div>

    <h3 style="margin-top:1.5rem">Counselors</h3>
    <div class="action-cards" style="margin-top:0.5rem">
        @foreach (['CMS-CNS-2001' => 'Wajeeha Nabeel, Campus Counselor', 'CMS-CNS-2002' => 'Hareem Jamil, Campus Counselor'] as $id => $name)
            <form method="POST" action="{{ route('dev.sso.mint') }}">
                @csrf
                <input type="hidden" name="role" value="counselor">
                <input type="hidden" name="cms_id" value="{{ $id }}">
                <button type="submit" class="action-card" style="width:100%; text-align:left; border:1px solid var(--line); background:none; cursor:pointer;">
                    <h3><i class="fa-solid fa-user-doctor"></i> {{ $name }}</h3>
                    <p>{{ $id }}</p>
                </button>
            </form>
        @endforeach
    </div>

    <h3 style="margin-top:1.5rem">Faculty &amp; Staff</h3>
    <div class="action-cards" style="margin-top:0.5rem">
        @foreach (['CMS-FAC-3001' => 'Dr. Naveed Iqbal', 'CMS-FAC-3002' => 'Ms. Fatima Raza'] as $id => $name)
            <form method="POST" action="{{ route('dev.sso.mint') }}">
                @csrf
                <input type="hidden" name="role" value="faculty_staff">
                <input type="hidden" name="cms_id" value="{{ $id }}">
                <button type="submit" class="action-card" style="width:100%; text-align:left; border:1px solid var(--line); background:none; cursor:pointer;">
                    <h3><i class="fa-solid fa-briefcase"></i> {{ $name }}</h3>
                    <p>{{ $id }}</p>
                </button>
            </form>
        @endforeach
    </div>
@endsection
