<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BNU Counseling Portal')</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @vite(['resources/js/app.js'])
    <style>
        .auth-wrap { max-width: 420px; margin: 4rem auto; padding: 0 1rem; }
        .auth-card { background: var(--white); border: 1px solid var(--line); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); padding: 1.75rem; }
        .auth-card h2 { font-family: "Plus Jakarta Sans", sans-serif; color: var(--primary-900); margin-bottom: 1.25rem; }
        .auth-switch { text-align: center; margin-top: 1rem; color: var(--neutral-600); }
        .auth-switch a { color: var(--primary-700); font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="brand-block">
                <h1 class="logo">BNU Counseling Portal</h1>
                <p class="brand-sub">Center for Counseling and Psychological Well-being (CCPW)</p>
            </div>
        </div>
    </header>
    <div class="auth-wrap">
        <div class="auth-card">
            @if (session('status'))
                <div class="status-banner">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="status-banner error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</body>
</html>
