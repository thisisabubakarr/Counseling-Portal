<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BNU Counseling Portal') — Center for Counseling and Psychological Well-being (CCPW)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @vite(['resources/js/app.js'])
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-left">
                <button type="button" class="hamburger" id="hamburgerMenu" aria-label="Toggle menu">
                    <span></span><span></span><span></span>
                </button>
                <div class="brand-block">
                    <h1 class="logo">BNU Counseling Portal</h1>
                    <p class="brand-sub">Center for Counseling and Psychological Well-being (CCPW)</p>
                </div>
            </div>
            <div class="header-right">
                @auth('student')
                    <span>{{ auth('student')->user()->name }} ({{ auth('student')->user()->roll_number }})</span>
                    <form method="POST" action="{{ route('student.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
                    </form>
                @endauth
                @auth('counselor')
                    <span>{{ auth('counselor')->user()->name }}</span>
                    <form method="POST" action="{{ route('counselor.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
                    </form>
                @endauth
                @auth('faculty_staff')
                    <span>{{ auth('faculty_staff')->user()->name }} ({{ auth('faculty_staff')->user()->employee_id }})</span>
                    <form method="POST" action="{{ route('faculty.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            @auth('student')
                @include('partials.sidebar-student')
            @endauth
            @auth('counselor')
                @include('partials.sidebar-counselor')
            @endauth
            @auth('faculty_staff')
                @include('partials.sidebar-faculty')
            @endauth
        </ul>
    </aside>

    <main class="main-content @auth('student') with-sidebar @endauth @auth('counselor') with-sidebar @endauth @auth('faculty_staff') with-sidebar @endauth" id="mainContent">
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
    </main>

    <script>
        const hamburger = document.getElementById('hamburgerMenu');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        if (hamburger) hamburger.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);
    </script>
    @yield('scripts')
</body>
</html>
