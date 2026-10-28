<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'student'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'students'),
    ],

    'guards' => [
        'student' => [
            'driver' => 'session',
            'provider' => 'students',
        ],
        'counselor' => [
            'driver' => 'session',
            'provider' => 'counselors',
        ],
        'faculty_staff' => [
            'driver' => 'session',
            'provider' => 'faculty_staff',
        ],
    ],

    'providers' => [
        'students' => [
            'driver' => 'eloquent',
            'model' => App\Models\Student::class,
        ],
        'counselors' => [
            'driver' => 'eloquent',
            'model' => App\Models\Counselor::class,
        ],
        'faculty_staff' => [
            'driver' => 'eloquent',
            'model' => App\Models\FacultyStaff::class,
        ],
    ],

    'passwords' => [
        'students' => [
            'provider' => 'students',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'counselors' => [
            'provider' => 'counselors',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
