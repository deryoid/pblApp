<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Portal Publik SIMEP PBL Politeknik Negeri Tanah Laut">
    <meta name="author" content="Politeknik Negeri Tanah Laut">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PBL App - Politeknik Negeri Tanah Laut')</title>
    <link rel="icon" href="{{ asset('sbadmin2/img/logo-campus.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome Free -->
    <link href="{{ asset('sbadmin2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Base Styles -->
    <link href="{{ asset('sbadmin2/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --border: #e2e8f0;
            --text-heading: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .public-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 0.875rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .brand-logo {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: clamp(1.1rem, 3vw, 1.25rem);
            color: #0f172a;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .brand-badge {
            font-size: 0.6875rem;
            font-weight: 700;
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            letter-spacing: 0.05em;
        }

        .nav-link-custom {
            color: #475569;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .nav-link-custom:hover {
            color: #2563eb;
            background: #f1f5f9;
        }

        .nav-link-custom.active {
            color: #2563eb;
            background: #eff6ff;
            font-weight: 700;
        }

        .public-mobile-drawer {
            border-top: 1px solid var(--border);
            background: #ffffff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            animation: drawerSlideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes drawerSlideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .btn-modern-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1.15rem;
            border-radius: 9999px;
            border: none;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .btn-modern-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.35);
        }

        /* Modernized Cards in Public Layout */
        .card {
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -2px rgba(0, 0, 0, 0.02);
            background: #ffffff;
        }

        .card-header {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            border-top-left-radius: 16px !important;
            border-top-right-radius: 16px !important;
            padding: 1rem 1.25rem;
        }

        .card-body {
            padding: clamp(1rem, 3vw, 1.5rem);
        }

        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            border-top: none;
            white-space: nowrap;
        }

        .table-responsive {
            border-radius: 12px;
            border: 1px solid var(--border);
            -webkit-overflow-scrolling: touch;
            overflow-x: auto;
        }

        /* Responsive typography helpers */
        h1, .h1 {
            font-size: clamp(1.2rem, 3.5vw, 1.65rem);
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        h2, .h2 {
            font-size: clamp(1.05rem, 3vw, 1.35rem);
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .text-wrap-neat {
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .public-footer {
            margin-top: auto;
            background: #0f172a;
            color: #94a3b8;
            padding: 2.5rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.875rem;
        }
    </style>

    @stack('styles')
</head>

<body id="page-top">

    <!-- Header Navigation -->
    <header class="public-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ route('home') }}" class="brand-logo">
                <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo" style="width:34px;height:auto;">
                <span>SIMEP <span class="brand-badge">PBL</span></span>
            </a>

            <!-- Desktop Links -->
            <div class="d-flex align-items-center">
                <a href="{{ route('home') }}" class="nav-link-custom d-none d-md-inline-flex {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="fas fa-th-large text-primary"></i> Showcase Proyek
                </a>
                <a href="{{ route('public.kunjungan.index') }}" class="nav-link-custom d-none d-md-inline-flex {{ request()->routeIs('public.kunjungan*') ? 'active' : '' }}">
                    <i class="fas fa-building text-info"></i> Kunjungan Mitra
                </a>

                @php
                    $dashboardUrl = null;
                    if (Auth::check()) {
                        $dashboardUrl = match(Auth::user()->role) {
                            'admin' => url('/admin'),
                            'evaluator' => url('/evaluator'),
                            default => url('/mahasiswa'),
                        };
                    }
                @endphp

                <div class="ml-2 ml-md-3 d-flex align-items-center">
                    @auth
                        <a href="{{ $dashboardUrl }}" class="btn-modern-primary d-none d-sm-inline-flex">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-modern-primary d-none d-sm-inline-flex">
                            <i class="fas fa-sign-in-alt"></i> Masuk
                        </a>
                    @endauth

                    <!-- Mobile Hamburger Toggle -->
                    <button class="btn btn-sm btn-outline-secondary d-md-none ml-2 rounded-lg" type="button" id="publicMobileMenuBtn" aria-label="Toggle menu" onclick="window.togglePublicMenu()" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                        <i class="fas fa-bars" id="publicMenuIcon"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="publicMobileMenu" class="public-mobile-drawer" style="display: none;">
            <div class="container py-3 d-flex flex-column gap-2">
                <a href="{{ route('home') }}" class="nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="fas fa-th-large text-primary"></i> Showcase Proyek (SPA)
                </a>
                <a href="{{ route('public.kunjungan.index') }}" class="nav-link-custom {{ request()->routeIs('public.kunjungan*') ? 'active' : '' }}">
                    <i class="fas fa-building text-info"></i> Kunjungan Mitra
                </a>
                <div class="pt-2 border-top mt-1">
                    @auth
                        <a href="{{ $dashboardUrl }}" class="btn-modern-primary w-100 justify-content-center">
                            <i class="fas fa-tachometer-alt"></i> Masuk ke Dashboard ({{ ucfirst(Auth::user()->role) }})
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-modern-primary w-100 justify-content-center">
                            <i class="fas fa-sign-in-alt"></i> Masuk ke Akun
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="py-4 flex-grow-1">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="container text-center">
            <p class="mb-1 text-white font-weight-bold">SIMEP &bull; Politeknik Negeri Tanah Laut</p>
            <p class="mb-0 text-muted small">&copy; {{ date('Y') }} Sistem Informasi Manajemen Evaluasi & Proyek PBL. All rights reserved.</p>
        </div>
    </footer>

    <!-- Core Scripts -->
    <script src="{{ asset('sbadmin2/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/js/sb-admin-2.min.js') }}"></script>

    <script>
        window.togglePublicMenu = function() {
            var menu = document.getElementById('publicMobileMenu');
            var icon = document.getElementById('publicMenuIcon');
            if (menu.style.display === 'none' || !menu.style.display) {
                menu.style.display = 'block';
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                menu.style.display = 'none';
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        };
    </script>

    @stack('scripts')
    @include('sweetalert::alert')
</body>
</html>
