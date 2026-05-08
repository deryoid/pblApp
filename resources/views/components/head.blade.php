<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SIMEP Politala</title>
    <link rel="icon" href="{{ asset('sbadmin2/img/logo-campus.png') }}" type="image/png">
    
    <!-- Preconnect to Google Fonts for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('/')}}sbadmin2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    
    <!-- Modern Premium Font (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('/')}}sbadmin2/css/sb-admin-2.min.css" rel="stylesheet">

    {{-- select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />  

    <!-- Custom styles for this page -->
    <link href="{{ asset('/')}}sbadmin2/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <!-- ============================================== -->
    <!-- GLOBAL PREMIUM UI OVERRIDES (SaaS Style)       -->
    <!-- ============================================== -->
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --text-dark: #0f172a;
        }

        body, .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        body {
            color: #334155;
            background-color: var(--bg-light);
        }

        /* ----- Sidebar Enhancements (Bright Visible Blue) ----- */
        .sidebar {
            background: #0ea5e9 !important; /* Sky Blue */
            background-image: linear-gradient(180deg, #0ea5e9 0%, #2563eb 100%) !important; /* Sky to Royal Blue */
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            border: none !important;
        }
        .sidebar .nav-item .nav-link {
            color: rgba(255,255,255,0.75);
            font-weight: 500;
            padding: 1rem 1.2rem;
            transition: all 0.2s ease-in-out;
        }
        .sidebar .nav-item.active .nav-link,
        .sidebar .nav-item .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255,255,255,0.1);
            border-left: 4px solid #ffffff;
        }
        .sidebar .nav-item .nav-link i {
            color: rgba(255,255,255,0.6);
        }
        .sidebar .nav-item.active .nav-link i,
        .sidebar .nav-item .nav-link:hover i {
            color: #ffffff;
        }
        .sidebar .sidebar-brand {
            color: #ffffff;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .sidebar hr.sidebar-divider {
            border-top: 1px solid rgba(255,255,255,0.15);
        }

        /* ----- Topbar Enhancements ----- */
        .topbar {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
            background-color: #ffffff !important;
            border-bottom: 1px solid var(--border-color);
        }

        /* ----- Card Enhancements ----- */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            transition: box-shadow 0.2s ease-in-out;
        }
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
            font-weight: 600;
            padding: 1.25rem 1.5rem;
        }
        .card-header .m-0.font-weight-bold.text-primary {
            color: var(--text-dark) !important;
            font-size: 1.1rem;
        }
        .card-body {
            padding: 1.5rem;
        }

        /* ----- Buttons Enhancements ----- */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.025em;
        }
        
        /* Mengubah button yang memiliki icon menjadi Murni Icon (tanpa teks) */
        .btn:has(i.fas, i.fa, i.far) {
            font-size: 0 !important; /* Menyembunyikan teks */
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            position: relative; /* Untuk mengunci posisi absolute anak */
            overflow: hidden;
        }
        .btn:has(i.fas, i.fa, i.far) i {
            font-size: 1rem !important; /* Mengembalikan ukuran icon */
            margin: 0 !important; /* Menghapus margin bawaan icon */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Memaksa ke titik tengah secara matematis */
        }
        
        /* Membersihkan komponen sisa bawaan SB Admin 2 */
        .btn:has(i.fas, i.fa, i.far) .text {
            display: none !important;
        }
        .btn:has(i.fas, i.fa, i.far) .icon {
            background: transparent !important;
            padding: 0 !important;
            display: contents !important; /* Mengabaikan wrapper */
        }

        /* Pengecualian jika button butuh lebar penuh seperti di form login */
        .btn-block:has(i) {
            width: 100% !important;
            font-size: 0.95rem !important;
        }
        .btn-block:has(i) i {
            position: relative;
            transform: none;
            top: auto;
            left: auto;
            margin-right: 8px !important;
        }

        .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            box-shadow: 0 1px 2px 0 rgba(79, 70, 229, 0.3);
        }
        .btn-primary:hover {
            background-color: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3), 0 2px 4px -1px rgba(79, 70, 229, 0.2);
            transform: translateY(-1px);
        }
        .btn-success { background-color: #10b981 !important; border-color: #10b981 !important; }
        .btn-success:hover { background-color: #059669 !important; border-color: #059669 !important; }

        /* ----- Form Controls Enhancements ----- */
        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.6rem 1rem;
            color: var(--text-dark);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        /* ----- Tables Enhancements ----- */
        .table {
            color: #4b5563;
        }
        .table thead th {
            background-color: #f9fafb;
            color: #6b7280;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            border-top: none;
            padding: 1rem;
        }
        .table tbody td {
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem;
        }
        .table-responsive {
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .table-bordered {
            border: none;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid var(--border-color);
        }

        /* ----- Soft Badges Enhancements ----- */
        .badge {
            padding: 0.4em 0.8em;
            font-weight: 600;
            border-radius: 6px;
            letter-spacing: 0.02em;
        }
        .badge-primary { background-color: #eef2ff !important; color: #4f46e5 !important; border: none; }
        .badge-success { background-color: #ecfdf5 !important; color: #059669 !important; border: none; }
        .badge-warning { background-color: #fffbeb !important; color: #d97706 !important; border: none; }
        .badge-danger { background-color: #fef2f2 !important; color: #dc2626 !important; border: none; }
        .badge-info { background-color: #eff6ff !important; color: #2563eb !important; border: none; }

        /* ----- Select2 Overrides ----- */
        .select2-container--default .select2-selection--single {
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            height: 42px !important;
            padding: 0.35rem 0.8rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }
        .select2-dropdown {
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Main Background Content Wrapper */
        #wrapper #content-wrapper {
            background-color: var(--bg-light);
        }

        /* Page Heading */
        .d-sm-flex.align-items-center.justify-content-between.mb-4 h1 {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.5rem;
        }
    </style>
    @stack('styles')
</head>