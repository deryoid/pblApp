<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Masuk ke SIMEP Politeknik Negeri Tanah Laut">
    <meta name="author" content="Politeknik Negeri Tanah Laut">
    
    <title>Masuk | SIMEP Politala</title>
    <link rel="icon" href="{{ asset('sbadmin2/img/logo-campus.png') }}" type="image/png">
    
    <!-- Preconnect & Fonts -->
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
            --primary-light: #eff6ff;
            --border: #e2e8f0;
            --text-heading: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(0.875rem, 3vw, 1.75rem);
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle ambient glows */
        .ambient-glow-1 {
            position: fixed;
            top: -10%;
            right: -5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, rgba(37, 99, 235, 0) 70%);
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
        }

        .ambient-glow-2 {
            position: fixed;
            bottom: -15%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, rgba(14, 165, 233, 0) 70%);
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 10;
            margin: 0 auto;
        }

        /* Top Nav to Showcase */
        .top-back-bar {
            text-align: center;
            margin-bottom: 1.25rem;
        }

        .back-showcase-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            color: #93c5fd;
            font-size: clamp(0.75rem, 2.5vw, 0.8125rem);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            text-align: center;
            max-width: 100%;
            word-break: break-word;
        }

        .back-showcase-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Login Card */
        .auth-card {
            background: #ffffff;
            border-radius: clamp(16px, 4vw, 24px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-card-body {
            padding: clamp(1.5rem, 5vw, 2.5rem) clamp(1.15rem, 4vw, 2.25rem);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-logo-img {
            width: clamp(52px, 8vw, 64px);
            height: clamp(52px, 8vw, 64px);
            object-fit: contain;
            margin-bottom: 0.75rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.06));
        }

        .brand-header h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(1.25rem, 4vw, 1.5rem);
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
            word-break: break-word;
        }

        .brand-header p {
            color: #64748b;
            font-size: clamp(0.8125rem, 2.5vw, 0.875rem);
            line-height: 1.4;
        }

        .form-label-custom {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 0.35rem;
            display: block;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .input-group-custom i.input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9375rem;
        }

        .form-control-modern {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.6rem;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            font-size: 0.9375rem;
            font-family: inherit;
            color: #0f172a;
            transition: all 0.2s;
            outline: none;
        }

        .form-control-modern:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .password-toggle-btn {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            font-size: 0.875rem;
        }

        .password-toggle-btn:hover {
            color: #334155;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .btn-submit-login {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            padding: 0.875rem;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit-login:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
            color: #ffffff;
        }

        .divider-box {
            position: relative;
            text-align: center;
            margin: 1.75rem 0;
        }

        .divider-box::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .divider-text {
            position: relative;
            background: #ffffff;
            padding: 0 0.875rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }

        .btn-google-oauth {
            width: 100%;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }

        .btn-google-oauth:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
            transform: translateY(-1px);
        }

        /* Quick Action Section */
        .quick-actions-box {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-quick-public {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
            padding: 0.625rem 0.875rem;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            word-break: break-word;
            line-height: 1.35;
        }

        .btn-quick-public:hover {
            background: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
        }

        .footer-note {
            text-align: center;
            color: #94a3b8;
            font-size: 0.8125rem;
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="login-container">
        
        <!-- Back to Showcase SPA -->
        <div class="top-back-bar">
            <a href="{{ route('home') }}" class="back-showcase-link">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Showcase Proyek Mahasiswa</span>
            </a>
        </div>

        <div class="auth-card">
            <div class="auth-card-body">
                
                <div class="brand-header">
                    <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo SIMEP" class="brand-logo-img">
                    <h1>SIMEP POLITALA</h1>
                    <p>Sistem Informasi Manajemen Evaluasi & Proyek PBL</p>
                </div>

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf
                    
                    <div class="input-group-custom">
                        <label class="form-label-custom">Nama Pengguna (Username)</label>
                        <i class="fas fa-user input-icon" style="top: 38px;"></i>
                        <input 
                            type="text" 
                            name="username" 
                            class="form-control-modern" 
                            placeholder="Masukkan username Anda" 
                            value="{{ old('username') }}" 
                            required 
                            autofocus
                        >
                    </div>

                    <div class="input-group-custom">
                        <label class="form-label-custom">Kata Sandi (Password)</label>
                        <i class="fas fa-lock input-icon" style="top: 38px;"></i>
                        <input 
                            type="password" 
                            id="passwordInput"
                            name="password" 
                            class="form-control-modern" 
                            placeholder="Masukkan kata sandi" 
                            required
                        >
                        <button type="button" class="password-toggle-btn" style="top: 38px;" onclick="window.togglePasswordVisibility()">
                            <i id="passwordEyeIcon" class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="remember-row">
                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer; color: #475569;">
                            <input type="checkbox" name="remember" value="1" style="width: 16px; height: 16px; accent-color: #2563eb;">
                            <span>Ingat sesi saya</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-submit-login">
                        <span>Masuk ke Akun</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="divider-box">
                        <span class="divider-text">atau masuk dengan</span>
                    </div>

                    <!-- Google SSO -->
                    <a href="{{ route('auth.google') }}" class="btn-google-oauth">
                        <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.616z" fill="#4285F4"/>
                            <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
                            <path d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.961H.957C.347 6.173 0 7.548 0 9s.347 2.827.957 4.039l3.007-2.332z" fill="#FBBC05"/>
                            <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z" fill="#EA4335"/>
                        </svg>
                        <span>Akun Google Kampus</span>
                    </a>
                </form>

                <div class="quick-actions-box">
                    <a href="{{ route('home') }}" class="btn-quick-public" style="background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;">
                        <i class="fas fa-th-large"></i>
                        <span>Lihat Galeri Proyek Selesai (Showcase)</span>
                    </a>
                    <a href="{{ route('public.kunjungan.index') }}" class="btn-quick-public">
                        <i class="fas fa-building text-info"></i>
                        <span>Portal Akses Mitra Industri</span>
                    </a>
                    <a href="https://drive.google.com/drive/folders/13FHP9XRBxzpABqo634BnXEqicdQzGdXF?usp=sharing" target="_blank" class="btn-quick-public">
                        <i class="fas fa-book text-warning"></i>
                        <span>Unduh Buku Pedoman PBL</span>
                    </a>
                </div>

            </div>
        </div>

        <p class="footer-note">
            &copy; {{ date('Y') }} Politeknik Negeri Tanah Laut. Hak Cipta Dilindungi.
        </p>

    </div>

    <!-- Scripts -->
    <script src="{{ asset('sbadmin2/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @include('sweetalert::alert')

    <script>
        window.togglePasswordVisibility = function() {
            const passInput = document.getElementById('passwordInput');
            const icon = document.getElementById('passwordEyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        };
    </script>
</body>
</html>
