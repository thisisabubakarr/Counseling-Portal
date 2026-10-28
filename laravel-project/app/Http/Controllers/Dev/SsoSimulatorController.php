<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Services\Cms\DevSsoKeyPair;
use Illuminate\Http\Request;

/**
 * Stands in for the BNU CMS during local development only. Registered in
 * routes/web.php behind app()->environment('local') — there is no
 * production route for this. Lets a developer simulate "log in to CMS,
 * then land on the student dashboard" or "click the Counselor tab" without
 * a real CMS to authenticate against yet.
 */
class SsoSimulatorController extends Controller
{
    public function index()
    {
        return view('dev.sso-simulator');
    }

    public function mint(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', 'in:student,counselor,faculty_staff'],
            'cms_id' => ['required', 'string'],
        ]);

        $token = DevSsoKeyPair::mintToken($data['role'], $data['cms_id']);

        return view('dev.sso-handoff', ['token' => $token]);
    }
}
