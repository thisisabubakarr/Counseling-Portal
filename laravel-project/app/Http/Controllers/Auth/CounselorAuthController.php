<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Login/registration are gone — counselors arrive only via CmsSsoController,
 * handed off from the BNU CMS's "Counselor" sidebar button. This controller
 * just ends the local session.
 */
class CounselorAuthController extends Controller
{
    public function logout(Request $request)
    {
        Auth::guard('counselor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
