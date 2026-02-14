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
        html, body {
            height: 100%;
        }

        body {
            padding-top: 76px; /* navbar height + spacing */
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            /* Kept the shadow and spacing, removed the dark gradient */
            background-color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .navbar-brand span {
            color: #333; /* Dark text for white background */
            letter-spacing: 0.5px;
        }
        
        /* Custom dropdown toggle style - Adapted for Light Background */
        .btn-user-menu {
            background-color: #f8f9fa;
            color: #555;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }
        .btn-user-menu:hover, .btn-user-menu:focus {
            background-color: #e9ecef;
            color: #333;
            border-color: #dee2e6;
            box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.03);
        }

        main {
            flex: 1; /* pushes footer to bottom */
        }

        footer {
            font-size: 12px;
            color: #777;
            text-align: center;
            padding: 10px 0;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top">
        <div class="container">
            <div class="navbar-brand">
                @auth
                    <span class="fw-bold fs-4">{{ Auth::user()->name }}</span>
                @else
                    <span class="fw-bold fs-4">نظام إدارة المولدات</span>
                @endauth
            </div>

            <div class="ms-auto">

                @auth
                    <div class="dropdown">
                        <button class="btn btn-user-menu rounded-circle dropdown-toggle no-arrow" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person-fill fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end text-end shadow-lg border-0 mt-2" aria-labelledby="userMenu">
                            <li>
                                <button type="button" class="dropdown-item py-2 px-3" 
                                        onclick="Livewire.dispatch('confirmPassword', { data: '{{ route('user.profile') }}' })">
                                    <i class="bi bi-person-gear me-2 text-primary"></i>تعديل معلومات الحساب
                                </button>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button class="dropdown-item text-danger py-2 px-3"><i class="bi bi-box-arrow-right me-2"></i>خروج</button>
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
        @livewire('password-confirmation-modal')
    </main>

    @yield('scripts')
    @stack('scripts')
    @livewireScripts

    <!-- Copywrite footer -->
    <footer>
        <div class="text-muted small">
            {{ config('nativephp.copyright') }}
        </div>
    </footer>
</body>
</html>
