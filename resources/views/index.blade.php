<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
    <meta name="description" content="Showcase Proyek Selesai PBL (Project-Based Learning) Politeknik Negeri Tanah Laut - Galeri Inovasi Mahasiswa">
    <meta name="author" content="Politeknik Negeri Tanah Laut">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>PBL Showcase | Galeri Inovasi & Proyek Mahasiswa Politeknik Negeri Tanah Laut</title>
    <link rel="icon" href="{{ asset('sbadmin2/img/logo-campus.png') }}" type="image/png">

    <!-- Preconnect Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <!-- FontAwesome Free -->
    <link href="{{ asset('sbadmin2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Modern App Styles -->
    <style>
        :root {
            --font-main: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-display: 'Space Grotesk', system-ui, sans-serif;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --accent: #0ea5e9;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-slate: #0b1120;
            --surface: #ffffff;
            --surface-muted: #f8fafc;
            --border: #e2e8f0;
            --text-heading: #0f172a;
            --text-body: #475569;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            overflow-x: hidden;
            font-family: var(--font-main);
            color: var(--text-body);
            background-color: #f8fafc;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        /* Responsive Container */
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 clamp(1rem, 3vw, 2rem);
        }

        /* Glassmorphism Navigation */
        .navbar-glass {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            transition: all 0.3s ease;
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: clamp(3.75rem, 7vw, 4.5rem);
            position: relative;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: clamp(0.5rem, 2vw, 0.875rem);
            text-decoration: none;
            color: var(--text-heading);
            min-width: 0;
        }

        .brand-logo-img {
            width: clamp(34px, 6vw, 44px);
            height: clamp(34px, 6vw, 44px);
            object-fit: contain;
            flex-shrink: 0;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.06));
        }

        .brand-title {
            font-family: var(--font-display);
            font-size: clamp(1.05rem, 3vw, 1.25rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .brand-badge {
            font-family: var(--font-main);
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            color: #ffffff;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            display: inline-block;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: clamp(1rem, 2vw, 1.75rem);
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            color: #475569;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
        }

        /* Mobile Menu Toggle Button */
        .mobile-toggle-btn {
            display: none;
            background: transparent;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-left: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .mobile-toggle-btn:hover {
            background: #f1f5f9;
        }

        /* Mobile Navigation Drawer */
        .mobile-nav-drawer {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            flex-direction: column;
            gap: 0.875rem;
            animation: drawerFade 0.2s ease;
        }

        .mobile-nav-drawer.active {
            display: flex;
        }

        @keyframes drawerFade {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .mobile-nav-link {
            text-decoration: none;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #334155;
            padding: 0.625rem 0.875rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: background 0.15s;
        }

        .mobile-nav-link:hover,
        .mobile-nav-link.active {
            background: #eff6ff;
            color: var(--primary);
        }

        /* Auth Button */
        .btn-auth {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: clamp(0.45rem, 1.5vw, 0.625rem) clamp(0.85rem, 2vw, 1.25rem);
            font-size: clamp(0.75rem, 1.8vw, 0.875rem);
            font-weight: 600;
            border-radius: 9999px;
            text-decoration: none;
            transition: all 0.25s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .btn-auth-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
        }

        .btn-auth-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
            color: #ffffff;
        }

        .btn-auth-dashboard {
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2);
        }

        .btn-auth-dashboard:hover {
            background: #1e293b;
            transform: translateY(-2px);
            color: #ffffff;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            color: #ffffff;
            padding: clamp(3rem, 7vw, 5.5rem) 0 clamp(2.5rem, 5vw, 4.5rem);
            overflow: hidden;
        }

        .hero-mesh {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(59, 130, 246, 0.2) 1px, transparent 1px), radial-gradient(rgba(14, 165, 233, 0.15) 1px, transparent 1px);
            background-size: 32px 32px;
            background-position: 0 0, 16px 16px;
            opacity: 0.6;
            pointer-events: none;
        }

        .hero-glow-1 {
            position: absolute;
            top: -10%;
            right: 5%;
            width: clamp(280px, 40vw, 480px);
            height: clamp(280px, 40vw, 480px);
            background: radial-gradient(circle, rgba(37, 99, 235, 0.35) 0%, rgba(37, 99, 235, 0) 70%);
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
        }

        .hero-glow-2 {
            position: absolute;
            bottom: -15%;
            left: -5%;
            width: clamp(240px, 35vw, 400px);
            height: clamp(240px, 35vw, 400px);
            background: radial-gradient(circle, rgba(14, 165, 233, 0.3) 0%, rgba(14, 165, 233, 0) 70%);
            border-radius: 50%;
            filter: blur(50px);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 860px;
            margin: 0 auto;
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 9999px;
            font-size: clamp(0.7rem, 2vw, 0.8125rem);
            font-weight: 600;
            color: #93c5fd;
            margin-bottom: clamp(1rem, 3vw, 1.5rem);
            backdrop-filter: blur(8px);
            max-width: 100%;
        }

        .hero-title {
            font-family: var(--font-display);
            font-size: clamp(1.85rem, 5.5vw, 2.85rem);
            line-height: 1.18;
            font-weight: 700;
            letter-spacing: -0.03em;
            margin-bottom: clamp(0.85rem, 2.5vw, 1.25rem);
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .hero-title span {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: clamp(0.925rem, 2.5vw, 1.125rem);
            color: #cbd5e1;
            margin-bottom: clamp(1.75rem, 4vw, 2.75rem);
            line-height: 1.65;
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 0.5rem;
        }

        /* Hero Search Bar */
        .hero-search-wrapper {
            max-width: 680px;
            margin: 0 auto clamp(2rem, 5vw, 3rem);
            position: relative;
            padding: 0 0.5rem;
        }

        .hero-search-box {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 9999px;
            padding: 0.4rem 0.5rem 0.4rem 1.25rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.25s ease;
            gap: 0.5rem;
        }

        .hero-search-box:focus-within {
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.4);
            transform: translateY(-2px);
        }

        .hero-search-input {
            flex: 1;
            min-width: 0;
            border: none;
            outline: none;
            font-size: clamp(0.875rem, 2.5vw, 1rem);
            font-family: var(--font-main);
            color: #0f172a;
            background: transparent;
        }

        .hero-search-input::placeholder {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .hero-search-btn {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            padding: clamp(0.6rem, 2vw, 0.75rem) clamp(1rem, 3vw, 1.5rem);
            font-size: clamp(0.8125rem, 2vw, 0.9375rem);
            font-weight: 600;
            border-radius: 9999px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .hero-search-btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: scale(1.02);
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: clamp(0.75rem, 2vw, 1.5rem);
            max-width: 960px;
            margin: 0 auto;
        }

        .metric-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: clamp(0.875rem, 2.5vw, 1.25rem) clamp(0.5rem, 2vw, 1rem);
            text-align: center;
            transition: transform 0.2s ease;
            min-width: 0;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.08);
        }

        .metric-val {
            font-family: var(--font-display);
            font-size: clamp(1.6rem, 4vw, 2.25rem);
            font-weight: 700;
            color: #60a5fa;
            line-height: 1.1;
            margin-bottom: 0.25rem;
        }

        .metric-label {
            font-size: clamp(0.6875rem, 1.8vw, 0.8125rem);
            font-weight: 600;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            line-height: 1.3;
            word-break: break-word;
        }

        /* SPA Controls & Filter Bar */
        .spa-controls-section {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 0;
            position: sticky;
            top: clamp(3.75rem, 7vw, 4.5rem);
            z-index: 40;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .controls-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.875rem;
        }

        /* Scheme Pills */
        .scheme-pills {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            max-width: 100%;
        }

        .scheme-pills::-webkit-scrollbar {
            display: none;
        }

        .pill-btn {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid transparent;
            padding: 0.45rem 0.95rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            flex-shrink: 0;
        }

        .pill-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .pill-btn.active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
        }

        /* Filters and Sort Dropdowns */
        .filter-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .select-filter {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 0.45rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #334155;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
            max-width: 100%;
        }

        .select-filter:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .view-toggle-group {
            display: flex;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .view-btn {
            background: #ffffff;
            border: none;
            padding: 0.45rem 0.7rem;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.85rem;
        }

        .view-btn.active {
            background: #2563eb;
            color: #ffffff;
        }

        /* Active Filter Info Banner */
        .filter-status-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.75rem 0 0.25rem;
            font-size: 0.8125rem;
            color: #64748b;
        }

        .reset-link {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: none;
            border: none;
            font-size: 0.8125rem;
        }

        .reset-link:hover {
            text-decoration: underline;
        }

        /* Showcase Project Cards Grid */
        .showcase-section {
            padding: clamp(1.75rem, 4vw, 3rem) 0 clamp(3rem, 6vw, 5rem);
            min-height: 50vh;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr));
            gap: clamp(1.25rem, 3vw, 2rem);
            transition: all 0.3s ease;
        }

        .projects-list {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* Project Card */
        .project-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
            position: relative;
            min-width: 0;
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            border-color: #cbd5e1;
        }

        /* Card Banner */
        .card-header-banner {
            padding: clamp(1rem, 2.5vw, 1.5rem);
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 1px solid #f1f5f9;
            position: relative;
            min-height: 105px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .banner-scheme-industri {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #a7f3d0;
        }

        .banner-scheme-pengabdian {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #bfdbfe;
        }

        .banner-scheme-lomba {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-color: #fde68a;
        }

        .banner-scheme-mandiri {
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            border-color: #e9d5ff;
        }

        .card-top-badges {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .badge-scheme {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .badge-scheme-industri { background: #10b981; color: #ffffff; }
        .badge-scheme-pengabdian { background: #2563eb; color: #ffffff; }
        .badge-scheme-lomba { background: #f59e0b; color: #ffffff; }
        .badge-scheme-mandiri { background: #8b5cf6; color: #ffffff; }

        .badge-status-done {
            background: rgba(16, 185, 129, 0.15);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.3);
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .pulse-indicator {
            width: 7px;
            height: 7px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.4);
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .card-topic-tag {
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.5rem;
            word-break: break-word;
            line-height: 1.35;
        }

        /* Card Body */
        .card-main-content {
            padding: clamp(1rem, 2.5vw, 1.5rem);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .project-title {
            font-family: var(--font-display);
            font-size: clamp(1.05rem, 2.5vw, 1.2rem);
            font-weight: 700;
            line-height: 1.38;
            color: #0f172a;
            margin-bottom: 0.65rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .project-description {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 1.15rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-word;
        }

        /* Mitra pill inside card */
        .mitra-badge-row {
            margin-bottom: 1rem;
            padding: 0.45rem 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.8rem;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            word-break: break-word;
            line-height: 1.3;
        }

        .mitra-badge-row strong {
            color: #0f172a;
        }

        /* Team & Matkul tags */
        .team-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 0.875rem;
            border-top: 1px solid #f1f5f9;
            gap: 0.5rem;
        }

        .team-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
        }

        .avatar-stack {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .member-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
            margin-left: -8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            flex-shrink: 0;
        }

        .member-avatar:first-child {
            margin-left: 0;
        }

        .team-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .team-sem {
            font-size: 0.6875rem;
            font-weight: 700;
            background: #e2e8f0;
            color: #475569;
            padding: 0.15rem 0.45rem;
            border-radius: 6px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* Card Footer Actions */
        .card-actions {
            padding: 0.875rem clamp(1rem, 2.5vw, 1.5rem);
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .btn-card-detail {
            flex: 1;
            min-height: 42px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            padding: 0.55rem;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-card-detail:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .btn-card-drive {
            min-height: 42px;
            min-width: 42px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 0.55rem 0.85rem;
            border-radius: 10px;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .btn-card-drive:hover {
            background: #f1f5f9;
            color: #2563eb;
            border-color: #93c5fd;
        }

        /* List View Overrides */
        .projects-list .project-card {
            flex-direction: row;
            border-radius: 16px;
        }

        .projects-list .card-header-banner {
            width: 260px;
            min-height: auto;
            border-bottom: none;
            border-right: 1px solid #e2e8f0;
        }

        .projects-list .card-main-content {
            padding: 1.25rem 1.5rem;
        }

        .projects-list .card-actions {
            width: 200px;
            flex-direction: column;
            justify-content: center;
            border-top: none;
            border-left: 1px solid #f1f5f9;
        }

        .projects-list .btn-card-detail,
        .projects-list .btn-card-drive {
            width: 100%;
        }

        /* Empty State */
        .empty-state-box {
            text-align: center;
            padding: 4rem 1.25rem;
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 20px;
            max-width: 580px;
            margin: 2rem auto;
        }

        .empty-icon {
            font-size: 3rem;
            color: #94a3b8;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .empty-desc {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        /* SPA Modal Drawer Responsive */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: clamp(0.5rem, 2vw, 1.25rem);
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-card {
            background: #ffffff;
            width: 100%;
            max-width: 820px;
            max-height: 92vh;
            border-radius: clamp(16px, 3vw, 24px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            transform: scale(0.96) translateY(8px);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal-overlay.active .modal-card {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            padding: clamp(1.25rem, 3vw, 1.75rem) clamp(1.25rem, 3vw, 2rem);
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            position: relative;
        }

        .modal-close-btn {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            background: rgba(255, 255, 255, 0.12);
            border: none;
            color: #ffffff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
            z-index: 10;
        }

        .modal-close-btn:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .modal-header-badges {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.65rem;
            padding-right: 2.5rem;
        }

        .modal-title {
            font-family: var(--font-display);
            font-size: clamp(1.15rem, 3.5vw, 1.5rem);
            font-weight: 700;
            line-height: 1.35;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        /* Modal Tabs */
        .modal-tabs {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 0 clamp(0.75rem, 2vw, 1.5rem);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            white-space: nowrap;
        }

        .modal-tabs::-webkit-scrollbar {
            display: none;
        }

        .modal-tab-btn {
            padding: 0.75rem 1rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            flex-shrink: 0;
        }

        .modal-tab-btn:hover {
            color: #0f172a;
        }

        .modal-tab-btn.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
            background: #ffffff;
        }

        .modal-body {
            padding: clamp(1.25rem, 3vw, 2rem);
            overflow-y: auto;
            flex: 1;
            -webkit-overflow-scrolling: touch;
        }

        .modal-tab-pane {
            display: none;
        }

        .modal-tab-pane.active {
            display: block;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Modal Content Styles */
        .detail-section-title {
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin-bottom: 0.65rem;
        }

        .detail-desc-box {
            font-size: clamp(0.875rem, 2vw, 0.95rem);
            line-height: 1.65;
            color: #334155;
            margin-bottom: 1.5rem;
            background: #f8fafc;
            padding: 1.15rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .detail-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            min-width: 0;
        }

        .detail-box-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .detail-box-val {
            font-size: 0.875rem;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
            line-height: 1.35;
        }

        /* Members List in Modal */
        .member-cards-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 250px), 1fr));
            gap: 0.875rem;
        }

        .member-item-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 0.875rem;
            min-width: 0;
        }

        .member-avatar-lg {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .member-meta-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            word-break: break-word;
        }

        .member-meta-sub {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.15rem;
        }

        .badge-role-ketua {
            background: #dbeafe;
            color: #1e40af;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            display: inline-block;
            margin-top: 0.25rem;
        }

        /* Tags in Modal */
        .tag-pill-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .tag-chip {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 0.3rem 0.65rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            word-break: break-word;
        }

        .modal-footer {
            padding: 1rem clamp(1.25rem, 3vw, 2rem);
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* Public Impact Section */
        .impact-section {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: clamp(3rem, 6vw, 5rem) 0;
        }

        .section-header {
            text-align: center;
            max-width: 680px;
            margin: 0 auto clamp(2rem, 5vw, 3.5rem);
        }

        .section-tag {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #2563eb;
            margin-bottom: 0.4rem;
        }

        .section-title {
            font-family: var(--font-display);
            font-size: clamp(1.6rem, 4.5vw, 2.25rem);
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            letter-spacing: -0.02em;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .impact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: clamp(1rem, 2.5vw, 2rem);
        }

        .impact-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: clamp(1.5rem, 3vw, 2.25rem) clamp(1.25rem, 2.5vw, 2rem);
            text-align: center;
            transition: all 0.3s ease;
        }

        .impact-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.06);
            border-color: #cbd5e1;
        }

        .impact-icon-box {
            width: clamp(52px, 8vw, 64px);
            height: clamp(52px, 8vw, 64px);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(1.4rem, 3vw, 1.75rem);
            margin: 0 auto 1.25rem;
        }

        .impact-icon-blue { background: #eff6ff; color: #2563eb; }
        .impact-icon-emerald { background: #ecfdf5; color: #10b981; }
        .impact-icon-amber { background: #fffbeb; color: #f59e0b; }

        .impact-heading {
            font-family: var(--font-display);
            font-size: clamp(1.1rem, 2.5vw, 1.25rem);
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.65rem;
            word-break: break-word;
        }

        .impact-text {
            font-size: 0.875rem;
            color: #64748b;
            line-height: 1.6;
            word-break: break-word;
        }

        /* Partners Section */
        .partners-section {
            padding: clamp(2.5rem, 5vw, 4rem) 0;
            background: #f8fafc;
            text-align: center;
        }

        .partner-chips-wrapper {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .partner-chip {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1.25rem;
            border-radius: 9999px;
            font-size: clamp(0.8125rem, 2vw, 0.9rem);
            font-weight: 600;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.2s;
            word-break: break-word;
            max-width: 100%;
        }

        .partner-chip:hover {
            border-color: #2563eb;
            color: #2563eb;
            transform: translateY(-2px);
        }

        /* Modern Footer */
        .site-footer {
            background: #0f172a;
            color: #94a3b8;
            padding: clamp(3rem, 6vw, 4.5rem) 0 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: clamp(1.75rem, 4vw, 3rem);
            margin-bottom: clamp(2rem, 5vw, 3.5rem);
        }

        .footer-brand-title {
            font-family: var(--font-display);
            font-size: clamp(1.15rem, 3vw, 1.375rem);
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .footer-brand-text {
            font-size: 0.875rem;
            line-height: 1.65;
            max-width: 440px;
            word-break: break-word;
        }

        .footer-heading {
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #ffffff;
            margin-bottom: 1rem;
        }

        .footer-links-list {
            list-style: none;
        }

        .footer-links-list li {
            margin-bottom: 0.65rem;
        }

        .footer-links-list a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
            word-break: break-word;
        }

        .footer-links-list a:hover {
            color: #ffffff;
        }

        .footer-bottom {
            padding-top: 1.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.8125rem;
        }

        /* Comprehensive Responsive Media Queries */
        @media (max-width: 992px) {
            .nav-links { display: none; }
            .mobile-toggle-btn { display: inline-flex; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
            .impact-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; gap: 2rem; }
            .projects-list .project-card { flex-direction: column; }
            .projects-list .card-header-banner,
            .projects-list .card-actions { width: 100%; border: none; }
        }

        @media (max-width: 768px) {
            .controls-wrapper { flex-direction: column; align-items: stretch; }
            .filter-actions { width: 100%; justify-content: space-between; }
            .select-filter { flex: 1 1 calc(50% - 0.5rem); min-width: 120px; }
            .view-toggle-group { display: none; } /* On mobile, cards are naturally stacked */
            .detail-grid { grid-template-columns: 1fr; }
            .hero-search-box { padding: 0.35rem 0.35rem 0.35rem 1rem; }
            .hero-search-btn span { display: none; } /* Icon only on mobile search button */
            .hero-search-btn { padding: 0.65rem 1rem; }
        }

        @media (max-width: 520px) {
            .brand-badge { display: none; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
            .metric-card { padding: 0.75rem 0.4rem; border-radius: 12px; }
            .select-filter { flex: 1 1 100%; }
            .filter-actions { flex-direction: column; }
            .modal-card { max-height: 95vh; }
            .modal-footer { flex-direction: column; align-items: stretch; }
            .modal-footer .btn-card-detail { width: 100%; }
        }

        @media (max-width: 360px) {
            .btn-auth span { display: none; }
            .btn-auth { padding: 0.5rem 0.65rem; }
            .brand-title { font-size: 1rem; }
        }
    </style>
</head>
<body>

    <!-- ============================================== -->
    <!-- NAVIGATION BAR (Glassmorphism + Mobile Menu)   -->
    <!-- ============================================== -->
    <header class="navbar-glass">
        <div class="container navbar-content">
            <a href="{{ route('home') }}" class="brand-link">
                <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Politala Logo" class="brand-logo-img">
                <div class="brand-title">
                    SIMEP <span class="brand-badge">PBL SHOWCASE</span>
                </div>
            </a>

            <!-- Desktop Links -->
            <nav>
                <ul class="nav-links">
                    <li><a href="#proyek" class="active"><i class="fas fa-th-large mr-1"></i> Galeri Proyek</a></li>
                    <li><a href="#dampak"><i class="fas fa-chart-line mr-1"></i> Dampak & Metrik</a></li>
                    <li><a href="#mitra"><i class="fas fa-handshake mr-1"></i> Mitra Industri</a></li>
                    <li><a href="{{ route('public.kunjungan.index') }}" target="_blank"><i class="fas fa-clipboard-check mr-1"></i> Kunjungan</a></li>
                </ul>
            </nav>

            <div class="d-flex align-items-center">
                @auth
                    @php
                        $user = Auth::user();
                        $dashUrl = match($user->role) {
                            'admin' => url('/admin'),
                            'evaluator' => url('/evaluator'),
                            'mahasiswa' => url('/mahasiswa'),
                            default => url('/'),
                        };
                    @endphp
                    <a href="{{ $dashUrl }}" class="btn-auth btn-auth-dashboard">
                        <i class="fas fa-user-circle"></i>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-auth btn-auth-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk Portal</span>
                    </a>
                @endauth

                <!-- Mobile Hamburger Toggle -->
                <button type="button" class="mobile-toggle-btn" id="mobileMenuToggle" aria-label="Toggle Navigation" onclick="window.toggleMobileMenu()">
                    <i class="fas fa-bars" id="mobileMenuIcon"></i>
                </button>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div class="mobile-nav-drawer" id="mobileNavDrawer">
                <a href="#proyek" class="mobile-nav-link" onclick="window.toggleMobileMenu()">
                    <i class="fas fa-th-large text-primary"></i> Galeri Proyek
                </a>
                <a href="#dampak" class="mobile-nav-link" onclick="window.toggleMobileMenu()">
                    <i class="fas fa-chart-line text-info"></i> Dampak & Metrik
                </a>
                <a href="#mitra" class="mobile-nav-link" onclick="window.toggleMobileMenu()">
                    <i class="fas fa-handshake text-success"></i> Mitra Industri
                </a>
                <a href="{{ route('public.kunjungan.index') }}" target="_blank" class="mobile-nav-link">
                    <i class="fas fa-clipboard-check text-warning"></i> Data Kunjungan Mitra
                </a>
            </div>
        </div>
    </header>

    <!-- ============================================== -->
    <!-- HERO SECTION                                   -->
    <!-- ============================================== -->
    <section class="hero-section">
        <div class="hero-mesh"></div>
        <div class="hero-glow-1"></div>
        <div class="hero-glow-2"></div>

        <div class="container">
            <div class="hero-content">
                <div class="hero-pill">
                    <i class="fas fa-rocket"></i>
                    <span>Pameran Karya Inovasi Mahasiswa Politala</span>
                </div>

                <h1 class="hero-title">
                    Eksplorasi Mahakarya <span>Proyek Berbasis Masalah</span> Nyata
                </h1>

                <p class="hero-subtitle">
                    Kumpulan solusi teknologi, rekayasa perangkat lunak, dan pemberdayaan masyarakat yang telah berhasil diselesaikan oleh mahasiswa Politeknik Negeri Tanah Laut bersama mitra industri dan institusi mitra.
                </p>

                <!-- Interactive Hero Search Box -->
                <div class="hero-search-wrapper">
                    <form id="heroSearchForm" onsubmit="event.preventDefault(); window.applySearchFilter();" class="hero-search-box">
                        <i class="fas fa-search text-muted mr-1 ml-1" style="color: #94a3b8;"></i>
                        <input 
                            type="text" 
                            id="globalSearchInput" 
                            class="hero-search-input" 
                            placeholder="Cari inovasi, topik, nama mitra, mahasiswa, atau kata kunci..."
                            autocomplete="off"
                        >
                        <button type="submit" class="hero-search-btn">
                            <span>Temukan</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>

                <!-- Live Metrics Grid -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-val" id="metricProjects">{{ $stats['total_completed'] }}</div>
                        <div class="metric-label">Proyek Selesai</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-val" id="metricMitra">{{ $stats['total_mitra'] }}</div>
                        <div class="metric-label">Mitra Industri</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-val" id="metricMhs">{{ $stats['total_mahasiswa'] }}</div>
                        <div class="metric-label">Mahasiswa Aktif</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-val" id="metricMatkul">{{ $stats['total_matkul'] }}</div>
                        <div class="metric-label">Mata Kuliah Terintegrasi</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- SPA CONTROLS BAR (Sticky Filter & Tabs)       -->
    <!-- ============================================== -->
    <section class="spa-controls-section" id="proyek">
        <div class="container">
            <div class="controls-wrapper">
                
                <!-- Scheme Filter Tabs -->
                <div class="scheme-pills" id="schemeFilterTabs">
                    <button class="pill-btn active" data-skema="all" onclick="window.setFilter('skema', 'all', this)">
                        <i class="fas fa-layer-group"></i> Semua Proyek
                    </button>
                    <button class="pill-btn" data-skema="Internal" onclick="window.setFilter('skema', 'Internal', this)">
                        <i class="fas fa-university"></i> Internal
                    </button>
                    <button class="pill-btn" data-skema="External" onclick="window.setFilter('skema', 'External', this)">
                        <i class="fas fa-building"></i> External
                    </button>
                    <button class="pill-btn" data-skema="Hibah/Penelitian" onclick="window.setFilter('skema', 'Hibah/Penelitian', this)">
                        <i class="fas fa-flask"></i> Hibah/Penelitian
                    </button>
                    <button class="pill-btn" data-skema="Lomba" onclick="window.setFilter('skema', 'Lomba', this)">
                        <i class="fas fa-trophy"></i> Lomba
                    </button>
                </div>

                <!-- Dropdowns & View Toggles -->
                <div class="filter-actions">
                    <!-- Semester Filter -->
                    <select id="semesterSelect" class="select-filter" onchange="window.setFilter('semester', this.value)">
                        <option value="all">Semua Semester</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem }}">Semester {{ $sem }}</option>
                        @endforeach
                    </select>

                    <!-- Periode Filter -->
                    <select id="periodeSelect" class="select-filter" onchange="window.setFilter('periode', this.value)">
                        <option value="all">Semua Periode</option>
                        @foreach($periodes as $per)
                            <option value="{{ $per->periode }}">{{ $per->periode }}</option>
                        @endforeach
                    </select>

                    <!-- Sort -->
                    <select id="sortSelect" class="select-filter" onchange="window.setSort(this.value)">
                        <option value="latest">Terbaru</option>
                        <option value="title_asc">Judul (A-Z)</option>
                        <option value="title_desc">Judul (Z-A)</option>
                    </select>

                    <!-- View Toggle -->
                    <div class="view-toggle-group">
                        <button id="viewGridBtn" class="view-btn active" onclick="window.setViewMode('grid')" title="Tampilan Grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button id="viewListBtn" class="view-btn" onclick="window.setViewMode('list')" title="Tampilan Daftar">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Active Filter Status -->
            <div class="filter-status-bar">
                <div>
                    Menampilkan <strong id="displayedCount" class="text-dark">0</strong> inovasi mahasiswa
                    <span id="activeFilterDesc" class="text-muted ml-1"></span>
                </div>
                <button type="button" class="reset-link" id="resetFiltersBtn" style="display: none;" onclick="window.resetAllFilters()">
                    <i class="fas fa-redo-alt"></i> Reset Filter
                </button>
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- SHOWCASE PROJECTS SECTION                      -->
    <!-- ============================================== -->
    <main class="showcase-section">
        <div class="container">

            <!-- Container for Project Cards -->
            <div id="projectsContainer" class="projects-grid">
                <!-- Projects rendered dynamically by SPA Engine below -->
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="emptyState" class="empty-state-box" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-search-minus"></i>
                </div>
                <h3 class="empty-title">Tidak Menemukan Proyek Selesai</h3>
                <p class="empty-desc">
                    Tidak ada proyek yang sesuai dengan kriteria pencarian atau filter yang Anda pilih saat ini.
                </p>
                <button class="hero-search-btn" onclick="window.resetAllFilters()" style="margin: 0 auto;">
                    <i class="fas fa-undo"></i> Tampilkan Semua Proyek
                </button>
            </div>

        </div>
    </main>

    <!-- ============================================== -->
    <!-- IMPACT & METRICS SECTION                       -->
    <!-- ============================================== -->
    <section class="impact-section" id="dampak">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">Dampak Pembelajaran Berbasis Proyek</div>
                <h2 class="section-title">Solusi Nyata Dari Kampus Vokasi Untuk Masyarakat</h2>
            </div>

            <div class="impact-grid">
                <div class="impact-card">
                    <div class="impact-icon-box impact-icon-blue">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="impact-heading">Kemitraan Industri</h3>
                    <p class="impact-text">
                        Mahasiswa memecahkan tantangan riil yang dihadapi mitra industri, UMKM, dan dinas pemerintahan melalui rancangan sistem tepat guna.
                    </p>
                </div>

                <div class="impact-card">
                    <div class="impact-icon-box impact-icon-emerald">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3 class="impact-heading">Keterampilan Standar Industri</h3>
                    <p class="impact-text">
                        Penerapan teknologi modern, agile workflow, evaluasi berkala oleh dosen penilai serta praktisi mitra industri secara langsung.
                    </p>
                </div>

                <div class="impact-card">
                    <div class="impact-icon-box impact-icon-amber">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="impact-heading">Luaran Berkelanjutan</h3>
                    <p class="impact-text">
                        Hasil proyek berupa software siap pakai, berkas dokumentasi teknis, serta publikasi dan pendaftaran perlindungan karya cipta/HAKI.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- PARTNERS RECOGNITION SECTION                   -->
    <!-- ============================================== -->
    <section class="partners-section" id="mitra">
        <div class="container">
            <div class="section-header" style="margin-bottom: 1.5rem;">
                <div class="section-tag">Kolaborasi & Kerjasama</div>
                <h2 class="section-title">Mitra yang Telah Berkolaborasi</h2>
            </div>

            <div class="partner-chips-wrapper" id="partnerChipsList">
                <div class="partner-chip"><i class="fas fa-building text-primary"></i> PT Borneo Tambak Lestari</div>
                <div class="partner-chip"><i class="fas fa-store text-success"></i> Rumah Kreatif Sasirangan Tanah Laut</div>
                <div class="partner-chip"><i class="fas fa-shield-alt text-danger"></i> BPBD Kabupaten Tanah Laut</div>
                <div class="partner-chip"><i class="fas fa-landmark text-info"></i> Pemerintah Desa Panggung</div>
                <div class="partner-chip"><i class="fas fa-school text-warning"></i> SMK Negeri 1 Pelaihari</div>
                <div class="partner-chip"><i class="fas fa-microscope text-primary"></i> Pusat Riset Vokasi Terapan</div>
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- INTERACTIVE SPA MODAL DETAIL                   -->
    <!-- ============================================== -->
    <div id="projectModal" class="modal-overlay" onclick="window.handleModalBackdropClick(event)">
        <div class="modal-card">
            
            <!-- Modal Header -->
            <div class="modal-header">
                <button class="modal-close-btn" onclick="window.closeProjectModal()" title="Tutup (ESC)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="modal-header-badges">
                    <span id="modalSchemeBadge" class="badge-scheme badge-scheme-industri">Skema</span>
                    <span id="modalStatusBadge" class="badge-status-done"><span class="pulse-indicator"></span> 100% Selesai</span>
                    <span id="modalPeriodeBadge" style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;"></span>
                </div>
                <h2 id="modalTitle" class="modal-title">Judul Proyek</h2>
            </div>

            <!-- Modal Navigation Tabs -->
            <div class="modal-tabs">
                <button class="modal-tab-btn active" data-tab="ringkasan" onclick="window.switchModalTab('ringkasan', this)">
                    <i class="fas fa-info-circle"></i> Ringkasan Proyek
                </button>
                <button class="modal-tab-btn" data-tab="tim" onclick="window.switchModalTab('tim', this)">
                    <i class="fas fa-users"></i> Tim Mahasiswa
                </button>
                <button class="modal-tab-btn" data-tab="mitra" onclick="window.switchModalTab('mitra', this)">
                    <i class="fas fa-handshake"></i> Mitra & Akademik
                </button>
                <button class="modal-tab-btn" data-tab="luaran" onclick="window.switchModalTab('luaran', this)">
                    <i class="fas fa-folder-open"></i> Luaran & Berkas
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                
                <!-- Tab 1: Ringkasan -->
                <div id="tab-ringkasan" class="modal-tab-pane active">
                    <div class="detail-section-title">Deskripsi & Ruang Lingkup Proyek</div>
                    <div id="modalDescription" class="detail-desc-box"></div>

                    <div class="detail-grid">
                        <div class="detail-box">
                            <div class="detail-box-label">Topik Proyek</div>
                            <div id="modalTopik" class="detail-box-val">-</div>
                        </div>
                        <div class="detail-box">
                            <div class="detail-box-label">Kelompok & Semester</div>
                            <div id="modalKelompokInfo" class="detail-box-val">-</div>
                        </div>
                        <div class="detail-box">
                            <div class="detail-box-label">Waktu Pengerjaan</div>
                            <div id="modalTimeline" class="detail-box-val">-</div>
                        </div>
                        <div class="detail-box">
                            <div class="detail-box-label">Status Akhir</div>
                            <div id="modalStatusProgress" class="detail-box-val text-success">
                                <i class="fas fa-check-circle"></i> Selesai (100%)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Tim Mahasiswa -->
                <div id="tab-tim" class="modal-tab-pane">
                    <div class="detail-section-title">Anggota Pengembang Proyek</div>
                    <div id="modalMembersList" class="member-cards-list">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <!-- Tab 3: Mitra & Akademik -->
                <div id="tab-mitra" class="modal-tab-pane">
                    <div class="detail-section-title">Informasi Mitra Kolaborasi</div>
                    <div class="detail-grid" style="margin-bottom: 1.5rem;">
                        <div class="detail-box">
                            <div class="detail-box-label">Nama Instansi / Mitra</div>
                            <div id="modalNamaMitra" class="detail-box-val">-</div>
                        </div>
                        <div class="detail-box">
                            <div class="detail-box-label">Kontak / PIC Mitra</div>
                            <div id="modalKontakMitra" class="detail-box-val">-</div>
                        </div>
                        <div class="detail-box" style="grid-column: 1 / -1;">
                            <div class="detail-box-label">Alamat Mitra</div>
                            <div id="modalAlamatMitra" class="detail-box-val">-</div>
                        </div>
                    </div>

                    <div class="detail-section-title">Mata Kuliah Terkait</div>
                    <div id="modalCoursesList" class="tag-pill-list" style="margin-bottom: 1.5rem;">
                        <!-- Filled by JS -->
                    </div>

                    <div class="detail-section-title">Dosen Penilai / Evaluator</div>
                    <div id="modalEvaluatorsList" class="tag-pill-list">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <!-- Tab 4: Luaran & Berkas -->
                <div id="tab-luaran" class="modal-tab-pane">
                    <div class="detail-section-title">Tautan Berkas & Luaran Proyek</div>
                    <div class="detail-desc-box">
                        Seluruh berkas luaran (laporan akhir, source code, manual book, dan dokumentasi video) disimpan dalam repositori terpusat.
                    </div>

                    <div id="modalDriveLinkWrapper" style="text-align: center; padding: 1.5rem 1rem;">
                        <!-- Button filled by JS -->
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <span class="text-muted" style="font-size: 0.8125rem;">
                    <i class="fas fa-university mr-1"></i> SIMEP Politeknik Negeri Tanah Laut
                </span>
                <button class="btn-card-detail" style="flex: initial; padding: 0.625rem 1.25rem;" onclick="window.closeProjectModal()">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    <!-- ============================================== -->
    <!-- SITE FOOTER                                    -->
    <!-- ============================================== -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand-title">
                        <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo" style="width:34px;height:auto;">
                        SIMEP Politala
                    </div>
                    <p class="footer-brand-text">
                        Sistem Informasi Manajemen Evaluasi & Proyek (SIMEP) Politeknik Negeri Tanah Laut. Platform kolaboratif integrasi kurikulum vokasi berbasis Problem Based Learning dengan standar industri.
                    </p>
                </div>

                <div>
                    <h4 class="footer-heading">Navigasi Publik</h4>
                    <ul class="footer-links-list">
                        <li><a href="#proyek">Galeri Proyek Selesai</a></li>
                        <li><a href="#dampak">Dampak & Capaian</a></li>
                        <li><a href="{{ route('public.kunjungan.index') }}">Data Kunjungan Mitra</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading">Portal Pengguna</h4>
                    <ul class="footer-links-list">
                        <li><a href="{{ route('login') }}">Masuk Portal Akun</a></li>
                        <li><a href="{{ route('login') }}">Masuk Dosen Penilai</a></li>
                        <li><a href="{{ route('login') }}">Masuk Mahasiswa</a></li>
                        <li><a href="https://drive.google.com/drive/folders/13FHP9XRBxzpABqo634BnXEqicdQzGdXF?usp=sharing" target="_blank">Pedoman PBL</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; {{ date('Y') }} Politeknik Negeri Tanah Laut. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div>
                    Didukung oleh Tim Pengembang SIMEP & SPM Politala
                </div>
            </div>
        </div>
    </footer>

    <!-- ============================================== -->
    <!-- SPA ENGINE REACTIVE LOGIC (Zero-latency)       -->
    <!-- ============================================== -->
    <script>
        // Data Bootstrap dari Controller
        window.SHOWCASE_PROJECTS = @json($projects);

        // State SPA
        window.SPA_STATE = {
            projects: window.SHOWCASE_PROJECTS || [],
            filterSkema: 'all',
            filterSemester: 'all',
            filterPeriode: 'all',
            searchQuery: '',
            sortBy: 'latest',
            viewMode: 'grid',
            activeProject: null,
            isMobileMenuOpen: false
        };

        // Mobile Menu Drawer Handler
        window.toggleMobileMenu = function() {
            const drawer = document.getElementById('mobileNavDrawer');
            const icon = document.getElementById('mobileMenuIcon');
            window.SPA_STATE.isMobileMenuOpen = !window.SPA_STATE.isMobileMenuOpen;

            if (window.SPA_STATE.isMobileMenuOpen) {
                drawer.classList.add('active');
                icon.className = 'fas fa-times';
            } else {
                drawer.classList.remove('active');
                icon.className = 'fas fa-bars';
            }
        };

        // Render Projects Engine
        window.renderProjects = function() {
            const container = document.getElementById('projectsContainer');
            const emptyState = document.getElementById('emptyState');
            const displayedCount = document.getElementById('displayedCount');
            const resetBtn = document.getElementById('resetFiltersBtn');
            const activeFilterDesc = document.getElementById('activeFilterDesc');

            if (!container) return;

            // Filter logic
            let filtered = window.SPA_STATE.projects.filter(p => {
                // Skema
                if (window.SPA_STATE.filterSkema !== 'all' && p.skema_pbl !== window.SPA_STATE.filterSkema) {
                    return false;
                }
                // Semester
                if (window.SPA_STATE.filterSemester !== 'all' && String(p.kelompok.semester) !== String(window.SPA_STATE.filterSemester)) {
                    return false;
                }
                // Periode
                if (window.SPA_STATE.filterPeriode !== 'all' && p.periode !== window.SPA_STATE.filterPeriode) {
                    return false;
                }
                // Search query
                if (window.SPA_STATE.searchQuery.trim() !== '') {
                    const q = window.SPA_STATE.searchQuery.toLowerCase();
                    const titleMatch = (p.title || '').toLowerCase().includes(q);
                    const descMatch = (p.description || '').toLowerCase().includes(q);
                    const topikMatch = (p.topik || '').toLowerCase().includes(q);
                    const mitraMatch = (p.nama_mitra || '').toLowerCase().includes(q);
                    const kelompokMatch = (p.kelompok.nama || '').toLowerCase().includes(q);
                    const memberMatch = (p.members || []).some(m => 
                        (m.nama || '').toLowerCase().includes(q) || (m.nim || '').toLowerCase().includes(q)
                    );

                    if (!titleMatch && !descMatch && !topikMatch && !mitraMatch && !kelompokMatch && !memberMatch) {
                        return false;
                    }
                }

                return true;
            });

            // Sorting logic
            if (window.SPA_STATE.sortBy === 'title_asc') {
                filtered.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
            } else if (window.SPA_STATE.sortBy === 'title_desc') {
                filtered.sort((a, b) => (b.title || '').localeCompare(a.title || ''));
            } else {
                // Latest by default (already in order)
            }

            // Update Status Counts
            displayedCount.textContent = filtered.length;

            const isFiltered = window.SPA_STATE.filterSkema !== 'all' || 
                               window.SPA_STATE.filterSemester !== 'all' || 
                               window.SPA_STATE.filterPeriode !== 'all' || 
                               window.SPA_STATE.searchQuery.trim() !== '';

            resetBtn.style.display = isFiltered ? 'inline-flex' : 'none';

            if (isFiltered) {
                let desc = [];
                if (window.SPA_STATE.filterSkema !== 'all') desc.push(`Skema: ${window.SPA_STATE.filterSkema}`);
                if (window.SPA_STATE.filterSemester !== 'all') desc.push(`Semester ${window.SPA_STATE.filterSemester}`);
                if (window.SPA_STATE.searchQuery.trim() !== '') desc.push(`Kata Kunci: "${window.SPA_STATE.searchQuery}"`);
                activeFilterDesc.textContent = `(${desc.join(' • ')})`;
            } else {
                activeFilterDesc.textContent = '';
            }

            // Handle Empty State
            if (filtered.length === 0) {
                container.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            // Set Container View Mode class
            container.className = (window.SPA_STATE.viewMode === 'list') ? 'projects-list' : 'projects-grid';

            // Build Cards HTML
            container.innerHTML = filtered.map((p, idx) => {
                const skemaSlug = (p.skema_pbl || 'mandiri').toLowerCase();
                const bannerClass = `banner-scheme-${skemaSlug}`;
                const badgeClass = `badge-scheme-${skemaSlug}`;

                // Initials for avatars
                const memberAvatars = (p.members || []).slice(0, 4).map(m => {
                    const initials = (m.nama || 'M')
                        .split(' ')
                        .map(n => n[0])
                        .slice(0, 2)
                        .join('')
                        .toUpperCase();
                    return `<div class="member-avatar" title="${escapeHtml(m.nama)} (${m.nim}) - ${m.role}">${initials}</div>`;
                }).join('');

                const mitraBadge = p.nama_mitra ? `
                    <div class="mitra-badge-row">
                        <i class="fas fa-building text-primary"></i>
                        <span>Mitra: <strong>${escapeHtml(p.nama_mitra)}</strong></span>
                    </div>
                ` : '';

                const driveBtn = p.link_drive ? `
                    <a href="${escapeHtml(p.link_drive)}" target="_blank" rel="noopener noreferrer" class="btn-card-drive" title="Buka Berkas Luaran Drive">
                        <i class="fab fa-google-drive"></i>
                    </a>
                ` : '';

                return `
                    <article class="project-card">
                        <div class="card-header-banner ${bannerClass}">
                            <div class="card-top-badges">
                                <span class="badge-scheme ${badgeClass}">
                                    <i class="fas fa-tag"></i> ${escapeHtml(p.skema_pbl)}
                                </span>
                                <span class="badge-status-done">
                                    <span class="pulse-indicator"></span> 100% Selesai
                                </span>
                            </div>
                            <div class="card-topic-tag">
                                <i class="fas fa-bookmark text-muted mr-1"></i>
                                <span>${escapeHtml(p.topik)}</span>
                            </div>
                        </div>

                        <div class="card-main-content">
                            <h3 class="project-title" title="${escapeHtml(p.title)}">
                                ${escapeHtml(p.title)}
                            </h3>

                            <p class="project-description">
                                ${escapeHtml(p.description)}
                            </p>

                            ${mitraBadge}

                            <div class="team-meta-row">
                                <div class="team-info">
                                    <div class="avatar-stack">
                                        ${memberAvatars}
                                    </div>
                                    <div style="min-width:0;">
                                        <div class="team-name">${escapeHtml(p.kelompok.nama)}</div>
                                    </div>
                                </div>
                                <span class="team-sem">Sem ${p.kelompok.semester || 1}</span>
                            </div>
                        </div>

                        <div class="card-actions">
                            <button type="button" class="btn-card-detail" onclick="window.openProjectModal('${p.uuid}')">
                                <i class="fas fa-expand-alt"></i> Detail Proyek
                            </button>
                            ${driveBtn}
                        </div>
                    </article>
                `;
            }).join('');
        };

        // Utility: Escape HTML
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // SPA Filter Handlers
        window.setFilter = function(key, val, el) {
            if (key === 'skema') {
                window.SPA_STATE.filterSkema = val;
                document.querySelectorAll('#schemeFilterTabs .pill-btn').forEach(b => b.classList.remove('active'));
                if (el) el.classList.add('active');
            } else if (key === 'semester') {
                window.SPA_STATE.filterSemester = val;
            } else if (key === 'periode') {
                window.SPA_STATE.filterPeriode = val;
            }
            window.renderProjects();
        };

        window.setSort = function(val) {
            window.SPA_STATE.sortBy = val;
            window.renderProjects();
        };

        window.setViewMode = function(mode) {
            window.SPA_STATE.viewMode = mode;
            document.getElementById('viewGridBtn')?.classList.toggle('active', mode === 'grid');
            document.getElementById('viewListBtn')?.classList.toggle('active', mode === 'list');
            window.renderProjects();
        };

        window.applySearchFilter = function() {
            const input = document.getElementById('globalSearchInput');
            window.SPA_STATE.searchQuery = input ? input.value : '';
            window.renderProjects();
            
            // Smooth scroll to projects section
            const el = document.getElementById('proyek');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        };

        // Real-time debounced typing search
        let searchTimeout;
        document.getElementById('globalSearchInput')?.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                window.SPA_STATE.searchQuery = e.target.value;
                window.renderProjects();
            }, 250);
        });

        window.resetAllFilters = function() {
            window.SPA_STATE.filterSkema = 'all';
            window.SPA_STATE.filterSemester = 'all';
            window.SPA_STATE.filterPeriode = 'all';
            window.SPA_STATE.searchQuery = '';
            window.SPA_STATE.sortBy = 'latest';

            document.getElementById('globalSearchInput').value = '';
            document.getElementById('semesterSelect').value = 'all';
            document.getElementById('periodeSelect').value = 'all';
            document.getElementById('sortSelect').value = 'latest';

            document.querySelectorAll('#schemeFilterTabs .pill-btn').forEach((b, i) => {
                b.classList.toggle('active', i === 0);
            });

            window.renderProjects();
        };

        // Modal Logic
        window.openProjectModal = function(uuid) {
            const project = window.SPA_STATE.projects.find(p => p.uuid === uuid);
            if (!project) return;

            window.SPA_STATE.activeProject = project;

            // Fill header
            document.getElementById('modalTitle').textContent = project.title;
            document.getElementById('modalPeriodeBadge').textContent = `Periode ${project.periode}`;

            const schemeBadge = document.getElementById('modalSchemeBadge');
            schemeBadge.textContent = project.skema_pbl;
            const skemaSlug = (project.skema_pbl || 'mandiri').toLowerCase();
            schemeBadge.className = `badge-scheme badge-scheme-${skemaSlug}`;

            // Tab 1: Ringkasan
            document.getElementById('modalDescription').textContent = project.description;
            document.getElementById('modalTopik').textContent = project.topik;
            document.getElementById('modalKelompokInfo').textContent = `${project.kelompok.nama} (Semester ${project.kelompok.semester || 1})`;
            document.getElementById('modalTimeline').textContent = `${project.tanggal_mulai || '-'} s/d ${project.tanggal_selesai || '-'}`;

            // Tab 2: Tim Mahasiswa
            const membersContainer = document.getElementById('modalMembersList');
            if (project.members && project.members.length > 0) {
                membersContainer.innerHTML = project.members.map(m => {
                    const initials = (m.nama || 'M').split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                    const roleBadge = m.role === 'Ketua' ? '<span class="badge-role-ketua">Ketua Kelompok</span>' : '<span style="font-size:0.75rem; color:#94a3b8;">Anggota</span>';
                    return `
                        <div class="member-item-card">
                            <div class="member-avatar-lg">${initials}</div>
                            <div style="min-width:0;">
                                <div class="member-meta-name">${escapeHtml(m.nama)}</div>
                                <div class="member-meta-sub">NIM: ${escapeHtml(m.nim)}</div>
                                ${roleBadge}
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                membersContainer.innerHTML = '<p class="text-muted">Data anggota mahasiswa belum terdata.</p>';
            }

            // Tab 3: Mitra & Akademik
            document.getElementById('modalNamaMitra').textContent = project.nama_mitra || 'Proyek Internal Kampus (Tanpa Mitra Luar)';
            document.getElementById('modalKontakMitra').textContent = project.kontak_mitra || '-';
            document.getElementById('modalAlamatMitra').textContent = project.alamat_mitra || '-';

            // Courses
            const coursesContainer = document.getElementById('modalCoursesList');
            if (project.courses && project.courses.length > 0) {
                coursesContainer.innerHTML = project.courses.map(c => `
                    <span class="tag-chip"><i class="fas fa-book mr-1"></i> ${escapeHtml(c.nama)} (${c.sks} SKS)</span>
                `).join('');
            } else {
                coursesContainer.innerHTML = '<span class="text-muted" style="font-size:0.875rem;">Mata kuliah capaian kurikulum PBL</span>';
            }

            // Evaluators
            const evalContainer = document.getElementById('modalEvaluatorsList');
            if (project.evaluators && project.evaluators.length > 0) {
                evalContainer.innerHTML = project.evaluators.map(e => `
                    <span class="tag-chip" style="background:#fef3c7; color:#92400e; border-color:#fde68a;">
                        <i class="fas fa-user-check mr-1"></i> ${escapeHtml(e)}
                    </span>
                `).join('');
            } else {
                evalContainer.innerHTML = '<span class="text-muted" style="font-size:0.875rem;">Dosen Tim Evaluator PBL Jurusan</span>';
            }

            // Tab 4: Luaran & Drive
            const driveWrapper = document.getElementById('modalDriveLinkWrapper');
            if (project.link_drive) {
                driveWrapper.innerHTML = `
                    <a href="${escapeHtml(project.link_drive)}" target="_blank" rel="noopener noreferrer" class="hero-search-btn" style="text-decoration:none; display:inline-flex; width:auto; max-width:100%;">
                        <i class="fab fa-google-drive mr-2"></i> Akses Berkas & Luaran Proyek di Drive
                    </a>
                `;
            } else {
                driveWrapper.innerHTML = `
                    <div style="color:#94a3b8; font-size:0.9375rem;">
                        <i class="fas fa-lock fa-2x mb-2" style="display:block;"></i>
                        Tautan drive luaran proyek tersimpan dalam arsip internal jurusan.
                    </div>
                `;
            }

            // Reset to Tab 1
            window.switchModalTab('ringkasan', document.querySelector('.modal-tab-btn[data-tab="ringkasan"]'));

            // Show modal
            const modal = document.getElementById('projectModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        window.closeProjectModal = function() {
            const modal = document.getElementById('projectModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        };

        window.handleModalBackdropClick = function(e) {
            if (e.target.id === 'projectModal') {
                window.closeProjectModal();
            }
        };

        window.switchModalTab = function(tabName, el) {
            document.querySelectorAll('.modal-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.modal-tab-pane').forEach(p => p.classList.remove('active'));

            if (el) el.classList.add('active');
            const targetPane = document.getElementById(`tab-${tabName}`);
            if (targetPane) targetPane.classList.add('active');
        };

        // Keyboard ESC to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeProjectModal();
            }
        });

        // Initialize on DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
            window.renderProjects();
        });
    </script>
</body>
</html>
