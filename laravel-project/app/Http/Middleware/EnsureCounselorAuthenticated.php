<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCounselorAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('counselor')->check()) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
