@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
    <div class="page-header">
        <h2>Tasks</h2>
    </div>

    <form method="POST" action="{{ route('counselor.tasks.store') }}" class="home-section">
        @csrf
        <div class="detail-grid">
            <div class="form-group">
                <label for="title">Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="date">Date <span class="required">*</span></label>
                <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
                <label for="time">Time <span class="optional-tag">(optional)</span></label>
                <input type="text" id="time" name="time" placeholder="14:00">
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description <span class="optional-tag">(optional)</span></label>
            <textarea id="description" name="description" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Add Task</button>
    </form>

    <div class="tasks-container" style="margin-top:1rem">
        @forelse ($tasks as $task)
            <div class="task-item {{ $task->status === 'done' ? 'task-done-row' : '' }}">
                <strong>{{ $task->title }}</strong> — {{ $task->date->format('M j, Y') }} {{ $task->time }}
                @if ($task->description)<p>{{ $task->description }}</p>@endif
                <div style="margin-top:0.5rem">
                    <form method="POST" action="{{ route('counselor.tasks.toggle', $task) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">{{ $task->status === 'done' ? 'Mark Pending' : 'Mark Done' }}</button>
                    </form>
                    <form method="POST" action="{{ route('counselor.tasks.destroy', $task) }}" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="empty-state">No tasks yet.</p>
        @endforelse
    </div>
@endsection
