<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="PBL TRKJ Politala - Sistem Informasi Manajemen Evaluasi & Proyek">
    <meta name="author" content="Dery Yuswanto Jaya, M.Kom - TRKJ Politala">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PBL TRKJ Politala')</title>
    <link rel="icon" href="{{ asset('sbadmin2/img/logo-campus.png') }}" type="image/png">
    
    <!-- Preconnect to Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- FontAwesome Free -->
    <link href="{{ asset('sbadmin2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    
    <!-- Modern Premium Fonts (Plus Jakarta Sans & Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- SB Admin 2 Base Styles -->
    <link href="{{ asset('sbadmin2/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />  

    <!-- DataTables CSS -->
    <link href="{{ asset('sbadmin2/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    
    <!-- ============================================== -->
    <!-- MODERN SAAS DESIGN SYSTEM OVERRIDES            -->
    <!-- ============================================== -->
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-soft: #eff6ff;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        body, .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            letter-spacing: -0.01em;
        }
        
        body {
            color: #334155;
            background-color: var(--bg-light);
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ----- Modern Sidebar ----- */
        .sidebar {
            background: var(--sidebar-bg) !important;
            background-image: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%) !important;
            box-shadow: 4px 0 20px rgba(0,0,0,0.06);
            border: none !important;
            transition: width 0.25s cubic-bezier(0.16, 1, 0.3, 1), transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1040;
        }

        .sidebar .sidebar-brand {
            height: 4.5rem;
            padding: 1.25rem 1rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.2s ease;
        }

        .sidebar .sidebar-brand-text {
            font-size: 1.125rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, #93c5fd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar .sidebar-heading {
            color: #64748b;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 1rem 1.25rem 0.5rem;
            white-space: nowrap;
        }

        .sidebar hr.sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin: 0.5rem 1rem;
        }

        .sidebar hr.sidebar-divider:last-of-type,
        .sidebar hr.sidebar-divider.d-none.d-md-block {
            display: none !important;
        }

        .sidebar .nav-item {
            position: relative;
        }

        .sidebar .nav-item .nav-link {
            color: #94a3b8;
            font-weight: 500;
            padding: 0.75rem 1.25rem;
            margin: 0.15rem 0.75rem;
            border-radius: 10px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            overflow: hidden;
            box-sizing: border-box;
        }

        .sidebar .nav-item .nav-link i {
            color: #64748b;
            font-size: 0.95rem;
            margin-right: 0.75rem;
            transition: color 0.2s;
            flex-shrink: 0;
        }

        .sidebar .nav-item .nav-link span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar .nav-item .nav-link:hover {
            color: #ffffff;
            background-color: var(--sidebar-hover);
        }

        .sidebar .nav-item .nav-link:hover i {
            color: #60a5fa;
        }

        .sidebar .nav-item.active .nav-link {
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .sidebar .nav-item.active .nav-link i {
            color: #ffffff;
        }

        /* Sidebar Collapse Inner */
        .sidebar .collapse-inner {
            background: #1e293b !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.5rem !important;
            margin: 0 0.75rem;
        }

        .sidebar .collapse-item {
            color: #cbd5e1 !important;
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            border-radius: 8px !important;
            padding: 0.5rem 0.875rem !important;
            transition: all 0.15s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar .collapse-item:hover,
        .sidebar .collapse-item.active {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
        }

        /* ======================================================== */
        /* SIDEBAR TOGGLED / COLLAPSED STATE (Desktop >= 768px)    */
        /* ======================================================== */
        @media (min-width: 768px) {
            .sidebar.toggled {
                width: 5.5rem !important;
                min-width: 5.5rem !important;
                overflow: visible !important;
            }

            .sidebar.toggled .sidebar-brand {
                width: 5.5rem !important;
                padding: 1.25rem 0 !important;
                justify-content: center !important;
            }

            .sidebar.toggled .sidebar-brand-text {
                display: none !important;
            }

            .sidebar.toggled .sidebar-brand-icon img {
                width: 32px !important;
                margin: 0 auto;
            }

            .sidebar.toggled .sidebar-heading {
                display: none !important;
            }

            .sidebar.toggled hr.sidebar-divider {
                width: 60% !important;
                margin: 0.5rem auto !important;
            }

            .sidebar.toggled .nav-item {
                width: 5.5rem !important;
                text-align: center;
            }

            .sidebar.toggled .nav-item .nav-link {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                width: 4rem !important;
                height: 3.75rem !important;
                min-height: 3.75rem !important;
                padding: 0.35rem 0.2rem !important;
                margin: 0.3rem auto !important;
                border-radius: 12px !important;
                text-align: center !important;
            }

            .sidebar.toggled .nav-item .nav-link i {
                font-size: 1.15rem !important;
                margin-right: 0 !important;
                margin-bottom: 0.25rem !important;
            }

            .sidebar.toggled .nav-item .nav-link span:not(.badge) {
                font-size: 0.65rem !important;
                line-height: 1.1 !important;
                font-weight: 500 !important;
                display: block !important;
                width: 100% !important;
                max-width: 3.75rem !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
                text-align: center !important;
            }

            .sidebar.toggled .nav-item .nav-link .badge {
                display: none !important;
            }

            .sidebar.toggled .nav-item .nav-link[data-toggle="collapse"]::after {
                display: none !important;
            }

            .sidebar.toggled .nav-item.active .nav-link {
                width: 4rem !important;
                margin: 0.3rem auto !important;
                background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4) !important;
            }

            /* Floating flyout for collapsed accordion */
            .sidebar.toggled .nav-item .collapse {
                position: absolute !important;
                left: calc(5.5rem + 0.5rem) !important;
                top: 0 !important;
                z-index: 1060 !important;
                margin: 0 !important;
            }

            .sidebar.toggled .nav-item .collapse .collapse-inner {
                min-width: 13rem !important;
                max-width: 16rem !important;
                margin: 0 !important;
                box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.2) !important;
                background: #1e293b !important;
                border: 1px solid rgba(255, 255, 255, 0.15) !important;
                border-radius: 14px !important;
            }
        }

        /* ======================================================== */
        /* MOBILE RESPONSIVE SIDEBAR (< 768px)                      */
        /* ======================================================== */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed !important;
                top: 0;
                bottom: 0;
                left: 0;
                height: 100vh !important;
                width: 15rem !important;
                max-width: 80vw !important;
                z-index: 1050 !important;
                transform: translateX(-100%);
                transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            .sidebar.toggled {
                transform: translateX(0) !important;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.35) !important;
            }

            .sidebar.toggled .sidebar-brand {
                height: 4.5rem;
                padding: 1.25rem 1rem;
                justify-content: flex-start !important;
            }

            .sidebar.toggled .sidebar-brand-text {
                display: inline-block !important;
            }

            .sidebar.toggled .sidebar-heading {
                display: block !important;
            }

            .sidebar.toggled .nav-item {
                width: 100% !important;
            }

            .sidebar.toggled .nav-item .nav-link {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                width: auto !important;
                margin: 0.15rem 0.75rem !important;
                padding: 0.75rem 1rem !important;
                text-align: left !important;
            }

            .sidebar.toggled .nav-item .nav-link i {
                margin-right: 0.75rem !important;
                margin-bottom: 0 !important;
                font-size: 0.95rem !important;
            }

            .sidebar.toggled .nav-item .nav-link span:not(.badge) {
                font-size: 0.85rem !important;
                display: inline !important;
                max-width: none !important;
                text-align: left !important;
            }

            .sidebar.toggled .nav-item .nav-link .badge {
                display: inline-block !important;
            }

            .sidebar.toggled .nav-item .nav-link[data-toggle="collapse"]::after {
                display: inline-block !important;
            }

            body.sidebar-toggled::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(2px);
                z-index: 1045;
                animation: fadeIn 0.2s ease-in-out;
            }
        }

        /* Sidebar Toggle Button */
        #sidebarToggle {
            width: 2.5rem !important;
            height: 2.5rem !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #94a3b8 !important;
            transition: all 0.2s ease !important;
            border-radius: 50% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        #sidebarToggle:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            transform: scale(1.05);
        }

        /* ----- Modern Topbar & Stacking Context ----- */
        .topbar {
            height: 4.5rem;
            position: relative !important;
            z-index: 1030 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
            background-color: #ffffff !important;
            border-bottom: 1px solid var(--border-color);
        }

        .topbar .navbar-nav {
            position: relative;
            z-index: 1035 !important;
        }

        .topbar .nav-item.dropdown {
            position: relative;
            z-index: 1040 !important;
        }

        /* Topbar User Profile Chip */
        #userDropdown {
            border: 1px solid transparent;
            padding: 0.35rem 0.65rem;
            border-radius: 12px;
            transition: all 0.15s ease-in-out;
        }

        #userDropdown:hover,
        #userDropdown[aria-expanded="true"] {
            background-color: #f1f5f9;
            border-color: #e2e8f0;
        }

        /* Topbar Dropdown Menu (Z-INDEX 1050 TO ALWAYS OVERLAY PAGE CARDS) */
        .topbar .dropdown-menu {
            position: absolute !important;
            z-index: 1050 !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            min-width: 260px !important;
            margin-top: 0.5rem !important;
            border-radius: 14px !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.06) !important;
            padding: 0.5rem !important;
        }

        .topbar .dropdown-menu .dropdown-item {
            border-radius: 8px !important;
            padding: 0.6rem 0.85rem !important;
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            color: #334155 !important;
            transition: all 0.15s ease !important;
            display: flex !important;
            align-items: center !important;
        }

        .topbar .dropdown-menu .dropdown-item:hover {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }

        .topbar .dropdown-menu .dropdown-item.text-danger:hover {
            background-color: #fef2f2 !important;
            color: #dc2626 !important;
        }

        /* Stacking context safety for page contents */
        #content-wrapper {
            position: relative;
            z-index: 1;
        }

        #content {
            position: relative;
            z-index: 1;
        }

        .container-fluid {
            position: relative;
            z-index: 1;
        }

        /* ----- Modern Cards ----- */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03), 0 1px 2px 0 rgba(0, 0, 0, 0.02);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .card:hover {
            box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            border-top-left-radius: 16px !important;
            border-top-right-radius: 16px !important;
            font-weight: 700;
            padding: 1.15rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .card-header .m-0.font-weight-bold.text-primary,
        .card-header h6 {
            color: var(--text-dark) !important;
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            word-break: break-word;
        }

        .card-body {
            padding: clamp(1rem, 2.5vw, 1.5rem);
        }

        /* Border Left Cards */
        .border-left-primary { border-left: 4px solid var(--primary-color) !important; }
        .border-left-success { border-left: 4px solid #10b981 !important; }
        .border-left-info    { border-left: 4px solid #0ea5e9 !important; }
        .border-left-warning { border-left: 4px solid #f59e0b !important; }
        .border-left-danger  { border-left: 4px solid #ef4444 !important; }

        /* ----- Modern Buttons ----- */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.55rem 1.15rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            letter-spacing: 0.01em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .btn-sm {
            padding: 0.4rem 0.85rem;
            font-size: 0.8125rem;
            border-radius: 8px;
        }

        .btn-xs {
            padding: 0.25rem 0.6rem;
            font-size: 0.75rem;
            border-radius: 6px;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af) !important;
            border-color: #1d4ed8 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
            color: #ffffff !important;
        }

        .btn-secondary {
            background: #475569 !important;
            border-color: #475569 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(71, 85, 105, 0.15);
        }

        .btn-secondary:hover {
            background: #334155 !important;
            border-color: #334155 !important;
            transform: translateY(-1px);
            color: #ffffff !important;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669) !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669, #047857) !important;
            border-color: #059669 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
            color: #ffffff !important;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            border-color: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
            border-color: #dc2626 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            color: #ffffff !important;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            border-color: #f59e0b !important;
            color: #ffffff !important;
        }

        .btn-info {
            background: linear-gradient(135deg, #0ea5e9, #0284c7) !important;
            border-color: #0ea5e9 !important;
            color: #ffffff !important;
        }

        .btn-outline-primary {
            color: var(--primary-color) !important;
            border: 1px solid #bfdbfe !important;
            background: #eff6ff !important;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color) !important;
            color: #ffffff !important;
            border-color: var(--primary-color) !important;
        }

        .btn-outline-warning {
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
            background: #fffbeb !important;
        }

        .btn-outline-warning:hover {
            background: #f59e0b !important;
            color: #ffffff !important;
            border-color: #f59e0b !important;
        }

        .btn-outline-success {
            color: #059669 !important;
            border: 1px solid #a7f3d0 !important;
            background: #ecfdf5 !important;
        }

        .btn-outline-success:hover {
            background: #10b981 !important;
            color: #ffffff !important;
            border-color: #10b981 !important;
        }

        .btn-outline-danger {
            color: #dc2626 !important;
            border: 1px solid #fecaca !important;
            background: #fef2f2 !important;
        }

        .btn-outline-danger:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            border-color: #ef4444 !important;
        }

        /* Split Icon Buttons Modern */
        .btn-icon-split {
            padding: 0 !important;
            overflow: hidden !important;
            display: inline-flex !important;
            align-items: stretch !important;
            border-radius: 9px !important;
            border: none !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
            vertical-align: middle !important;
        }

        .btn-icon-split .icon {
            background: rgba(0, 0, 0, 0.12) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0.45rem 0.75rem !important;
        }

        .btn-icon-split .text {
            padding: 0.45rem 0.85rem !important;
            font-weight: 600 !important;
            font-size: 0.825rem !important;
            display: inline-flex !important;
            align-items: center !important;
        }

        /* Action Icon Buttons for Tables */
        .btn-action-icon,
        .btn-circle.btn-sm {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            min-height: 32px !important;
            padding: 0 !important;
            margin: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 8px !important;
            font-size: 0.8rem !important;
            transition: all 0.15s ease-in-out !important;
            flex-shrink: 0 !important;
            vertical-align: middle !important;
        }

        .btn-action-icon:hover,
        .btn-circle.btn-sm:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.12) !important;
        }

        /* ----- Form Controls & Inputs Standardized ----- */
        .form-control,
        .custom-select {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding: 0.55rem 1rem;
            font-size: 0.875rem;
            color: var(--text-dark);
            background-color: #ffffff;
            transition: all 0.15s ease-in-out;
            height: 42px;
            line-height: 1.5;
        }

        textarea.form-control {
            height: auto !important;
            min-height: 90px;
        }

        select.form-control,
        select.custom-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: #ffffff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23475569' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.85rem center !important;
            background-size: 14px 10px !important;
            padding-right: 2.35rem !important;
            cursor: pointer;
        }

        select[multiple].form-control {
            height: auto !important;
            background-image: none !important;
            padding-right: 1rem !important;
        }

        .form-control:focus,
        .custom-select:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
            outline: none;
        }

        .form-control.is-invalid,
        .custom-select.is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
            display: inline-block;
        }

        .form-text,
        .text-muted.small {
            font-size: 0.775rem !important;
            color: #64748b !important;
            margin-top: 0.3rem;
        }

        /* ----- Modern Tables & DataTables ----- */
        .table {
            color: #334155;
            margin-bottom: 0;
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0;
        }

        .table-responsive {
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-bordered {
            border: none !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #f1f5f9 !important;
            border-top: none !important;
        }

        .table thead th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            font-weight: 700 !important;
            border-bottom: 2px solid var(--border-color) !important;
            border-top: none !important;
            padding: 0.875rem 1rem !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
        }

        .table tbody td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 0.8rem 1rem !important;
            font-size: 0.85rem !important;
            color: #334155;
        }

        .table-hover tbody tr:hover,
        .table tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* DataTables Controls Fixes */
        .dataTables_wrapper {
            font-family: inherit;
        }

        .dataTables_wrapper .row:first-child {
            margin-bottom: 1rem;
            align-items: center;
        }

        .dataTables_wrapper .row:last-child {
            margin-top: 1rem;
            align-items: center;
        }

        .dataTables_length {
            display: flex;
            align-items: center;
        }

        .dataTables_length label {
            display: inline-flex;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 0;
            gap: 0.4rem;
        }

        .dataTables_length select,
        .dataTables_length select.custom-select,
        .dataTables_length select.form-control {
            height: 38px !important;
            line-height: 1.5 !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            border-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            color: #334155 !important;
            padding: 0.35rem 1.75rem 0.35rem 0.75rem !important;
            min-width: 72px !important;
            display: inline-block !important;
            vertical-align: middle !important;
            box-shadow: none !important;
        }

        .dataTables_filter {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 0;
            width: 100%;
            justify-content: flex-end;
            gap: 0.4rem;
        }

        .dataTables_filter input,
        .dataTables_filter input.form-control {
            height: 38px !important;
            border-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 0.4rem 0.85rem !important;
            font-size: 0.85rem !important;
            color: #334155 !important;
            background-color: #ffffff !important;
            min-width: 220px !important;
            margin-left: 0 !important;
            transition: all 0.2s ease-in-out !important;
            box-shadow: none !important;
        }

        .dataTables_filter input:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
            outline: none !important;
        }

        .dataTables_info {
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            color: var(--text-muted) !important;
            padding-top: 0.5rem !important;
        }

        .dataTables_paginate {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .dataTables_paginate .pagination {
            margin: 0;
            gap: 0.25rem;
        }

        .dataTables_paginate .page-item .page-link {
            border-radius: 8px !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
            border: 1px solid #e2e8f0 !important;
            color: #475569 !important;
            background-color: #ffffff !important;
            transition: all 0.15s ease !important;
        }

        .dataTables_paginate .page-item.active .page-link {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25) !important;
        }

        .dataTables_paginate .page-item.disabled .page-link {
            opacity: 0.5 !important;
            background-color: #f8fafc !important;
            color: #94a3b8 !important;
            border-color: #e2e8f0 !important;
        }

        /* Action Column Container */
        .table-action-group {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        /* ----- Modern Badges ----- */
        .badge {
            padding: 0.35em 0.75em;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
            letter-spacing: 0.02em;
        }

        .badge-primary { background-color: #eff6ff !important; color: #1d4ed8 !important; border: 1px solid #bfdbfe; }
        .badge-success { background-color: #ecfdf5 !important; color: #065f46 !important; border: 1px solid #a7f3d0; }
        .badge-warning { background-color: #fffbeb !important; color: #92400e !important; border: 1px solid #fde68a; }
        .badge-danger  { background-color: #fef2f2 !important; color: #991b1b !important; border: 1px solid #fecaca; }
        .badge-info    { background-color: #f0f9ff !important; color: #075985 !important; border: 1px solid #bae6fd; }

        /* Select2 Overrides */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px !important;
            height: 42px !important;
            padding: 0.45rem 0.85rem !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            transition: all 0.15s ease-in-out !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
            padding-left: 0 !important;
            padding-right: 1.5rem !important;
            color: #1e293b !important;
            font-size: 0.875rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 10px !important;
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 42px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px !important;
            padding: 4px 8px !important;
            background-color: #ffffff !important;
            transition: all 0.15s ease-in-out !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            color: #1d4ed8 !important;
            border-radius: 6px !important;
            padding: 2px 8px !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
            margin-top: 3px !important;
            margin-right: 5px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #1d4ed8 !important;
            margin-right: 5px !important;
            font-weight: bold !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--multiple {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
            outline: none !important;
        }

        .select2-dropdown {
            border: 1px solid var(--border-color) !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
            z-index: 1060 !important;
            overflow: hidden;
        }

        .select2-results__option {
            font-size: 0.85rem !important;
            padding: 8px 12px !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color) !important;
            color: #ffffff !important;
        }

        /* Page Wrapper */
        #wrapper #content-wrapper {
            background-color: var(--bg-light);
        }

        /* Page Heading */
        .d-sm-flex.align-items-center.justify-content-between.mb-4 h1 {
            font-weight: 800;
            color: var(--text-dark);
            font-size: clamp(1.25rem, 3.5vw, 1.6rem);
            letter-spacing: -0.02em;
            word-break: break-word;
        }

        /* Responsive Utilities */
        .text-break-word {
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
        }

        @media (max-width: 576px) {
            .topbar {
                padding-left: 0.875rem !important;
                padding-right: 0.875rem !important;
            }
            .sidebar .nav-item .nav-link {
                padding: 0.65rem 0.875rem;
                margin: 0.1rem 0.5rem;
            }
            .table thead th, .table tbody td {
                padding: 0.65rem 0.75rem;
            }
            .btn {
                font-size: 0.8125rem;
            }
        }
        /* Anti-Slop Global Affordances & Accessibility (R-03, R-09, R-39) */
        button,
        [role="button"],
        .btn,
        .page-link,
        .custom-control-label,
        .custom-file-label,
        input[type="checkbox"],
        input[type="radio"],
        select,
        .table-action-group .btn-action-icon,
        [data-toggle="modal"],
        [data-toggle="dropdown"] {
            cursor: pointer;
        }

        .table-action-group {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .btn-action-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
        }

        @media (max-width: 768px) {
            .btn-action-icon {
                width: 44px;
                height: 44px;
                min-width: 44px;
                min-height: 44px;
                font-size: 0.95rem;
            }
        }

        .badge-status-linked {
            background-color: #ecfdf5 !important;
            color: #065f46 !important;
            border: 1px solid #a7f3d0 !important;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-status-unlinked {
            background-color: #fffbeb !important;
            color: #92400e !important;
            border: 1px solid #fde68a !important;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
        }
    </style>
    @stack('styles')
</head>
