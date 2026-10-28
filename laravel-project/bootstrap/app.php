<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'student.auth' => \App\Http\Middleware\EnsureStudentAuthenticated::class,
            'counselor.auth' => \App\Http\Middleware\EnsureCounselorAuthenticated::class,
            'faculty.auth' => \App\Http\Middleware\EnsureFacultyStaffAuthenticated::class,
        ]);

        // The SSO callback is a genuine cross-origin POST from the CMS — there is no
        // session-bound CSRF token to check. The signed RS256 token is the auth proof.
        $middleware->validateCsrfTokens(except: [
            'sso/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
