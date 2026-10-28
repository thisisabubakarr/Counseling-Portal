<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Support\CodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $counselor = Auth::guard('counselor')->user();

        $tasks = Task::where(function ($q) use ($counselor) {
            $q->where('counselor_id', $counselor->id)->orWhereNull('counselor_id');
        })->orderBy('date')->orderBy('time')->get();

        return view('counselor.tasks.index', ['tasks' => $tasks]);
    }

    public function store(Request $request)
    {
        $counselor = Auth::guard('counselor')->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['nullable', 'string'],
        ]);

        Task::create($data + [
            'task_code' => CodeGenerator::make('TASK'),
            'counselor_id' => $counselor->id,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Task created.');
    }

    public function toggle(Task $task)
    {
        $task->update(['status' => $task->status === 'done' ? 'pending' : 'done']);

        return back()->with('status', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return back()->with('status', 'Task deleted.');
    }
}
