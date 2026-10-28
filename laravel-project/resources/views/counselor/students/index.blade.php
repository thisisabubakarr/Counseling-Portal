@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <div class="page-header">
        <h2>Students</h2>
    </div>

    <form method="GET" style="margin-bottom:1rem;max-width:24rem">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or roll number" class="form-control">
    </form>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Roll Number</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Risk</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->roll_number }}</td>
                        <td>{{ $student->department }}</td>
                        <td><span class="status-badge status-{{ \Illuminate\Support\Str::slug($student->session_status) }}">{{ $student->session_status }}</span></td>
                        <td>@if ($student->suicide_alert)<span class="status-badge suicide-alert-label">Alert</span>@endif</td>
                        <td><a href="{{ route('counselor.students.show', $student) }}" class="btn btn-primary btn-sm">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $students->links() }}
@endsection
