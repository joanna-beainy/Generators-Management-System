<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @livewireStyles
    <style>
        body {
            padding-top: 70px; /* Prevent content overlap with fixed navbar */
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <div class="navbar-brand">
                @auth
                    <span class="fw-bold">{{ Auth::user()->name }}</span>
                @else
                    <span class="fw-bold">نظام إدارة المولدات</span>
                @endauth
            </div>

            <div class="ms-auto">

                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end text-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="{{ route('user.profile') }}"><i class="bi bi-person-gear me-2"></i>تعديل معلومات الحساب</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>خروج</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container">
        @yield('content')
    </main>

    @yield('scripts')
    @stack('scripts')
    @livewireScripts
</body>
</html>
