@extends('layouts.app')

@section('title', 'Faculty & Staff')

@section('content')
    <div class="page-header">
        <h2>Faculty & Staff</h2>
    </div>

    <form method="GET" style="margin-bottom:1rem;max-width:24rem">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or employee ID" class="form-control">
    </form>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Employee ID</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($facultyStaff as $person)
                    <tr>
                        <td>{{ $person->name }}</td>
                        <td>{{ $person->employee_id }}</td>
                        <td>{{ $person->designation }}</td>
                        <td>{{ $person->department }}</td>
                        <td><a href="{{ route('counselor.faculty-staff.show', $person) }}" class="btn btn-primary btn-sm">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No faculty or staff found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $facultyStaff->links() }}
@endsection
