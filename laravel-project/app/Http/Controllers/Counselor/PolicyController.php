<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use App\Support\CodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PolicyController extends Controller
{
    private const RESERVED_SLUGS = ['consent', 'drugs', 'appointments'];

    public function index()
    {
        $policies = Policy::orderedForDisplay()->get();

        return view('counselor.policy.index', [
            'policies' => $policies,
            'reservedSlugs' => self::RESERVED_SLUGS,
        ]);
    }

    public function store(Request $request)
    {
        $counselor = Auth::guard('counselor')->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $slug = $this->uniqueSlug($data['title']);

        Policy::create([
            'policy_code' => CodeGenerator::make('POLICY'),
            'slug' => $slug,
            'title' => trim($data['title']),
            'description' => isset($data['description']) ? trim($data['description']) : null,
            'body' => $data['body'],
            'counselor_id' => $counselor->id,
            'counselor_name' => $counselor->name,
        ]);

        return back()->with('status', 'Policy published.');
    }

    public function update(Request $request, Policy $policy)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        // Slug is never accepted from the request — the standard documents
        // (consent/drugs/appointments) must keep the slug the rest of the
        // app looks them up by, even while their title/body are editable.
        $policy->update([
            'title' => trim($data['title']),
            'description' => isset($data['description']) ? trim($data['description']) : null,
            'body' => $data['body'],
        ]);

        return back()->with('status', 'Policy updated.');
    }

    public function destroy(Policy $policy)
    {
        $policy->delete();

        return back()->with('status', 'Policy deleted.');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'policy';
        $slug = $base;
        $suffix = 2;

        while (
            in_array($slug, self::RESERVED_SLUGS, true)
            || Policy::where('slug', $slug)->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
