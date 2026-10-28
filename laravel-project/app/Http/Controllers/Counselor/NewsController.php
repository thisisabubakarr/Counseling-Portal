<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Support\CodeGenerator;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::where('published', true)->latest()->paginate(20);

        return view('counselor.news.index', ['news' => $news]);
    }

    public function store(Request $request)
    {
        $counselor = Auth::guard('counselor')->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'event_date' => ['nullable', 'date_format:Y-m-d'],
            'notify_students' => ['nullable', 'boolean'],
        ]);

        $item = News::create([
            'news_code' => CodeGenerator::make('NEWS'),
            'title' => trim($data['title']),
            'body' => trim($data['body']),
            'event_date' => $data['event_date'] ?? null,
            'counselor_id' => $counselor->id,
            'counselor_name' => $counselor->name,
            'published' => true,
        ]);

        if ($data['notify_students'] ?? true) {
            Notifier::toStudentBroadcast(
                "CCPW Event: {$item->title}",
                Str::limit($item->body, 280),
                'info',
                ['news_id' => $item->id, 'event_date' => $item->event_date?->toDateString(), 'broadcast' => true]
            );
        }

        return back()->with('status', 'News published.');
    }

    public function destroy(News $news)
    {
        $news->delete();

        return back()->with('status', 'News deleted.');
    }
}
