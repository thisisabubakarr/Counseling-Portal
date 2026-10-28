<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Faculty/staff arrive only via CmsSsoController, handed off from the BNU
 * CMS. This controller just ends the local session.
 */
class FacultyStaffAuthController extends Controller
{
    public function logout(Request $request)
    {
        Auth::guard('faculty_staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
