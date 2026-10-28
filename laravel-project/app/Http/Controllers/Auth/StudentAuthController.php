<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Login/registration are gone — students arrive only via CmsSsoController,
 * handed off from the BNU CMS. This controller just ends the local session.
 */
class StudentAuthController extends Controller
{
    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
