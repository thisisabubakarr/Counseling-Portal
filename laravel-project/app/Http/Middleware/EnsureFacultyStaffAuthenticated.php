<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFacultyStaffAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('faculty_staff')->check()) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
