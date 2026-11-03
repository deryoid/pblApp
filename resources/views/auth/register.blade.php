<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Pendaftaran Akun Mahasiswa - PBL TRKJ Politala">
    <meta name="author" content="Dery Yuswanto Jaya, M.Kom - TRKJ Politala">
    
    <title>Pendaftaran Mahasiswa | PBL TRKJ Politala</title>
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

        .register-container {
            width: 100%;
            max-width: 540px;
            position: relative;
            z-index: 10;
            margin: auto;
        }

        .top-back-bar {
            margin-bottom: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-link {
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .back-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            text-decoration: none;
            transform: translateX(-2px);
        }

        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .auth-card-body {
            padding: clamp(1.5rem, 5vw, 2.5rem);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-logo-img {
            width: 52px;
            height: auto;
            margin-bottom: 0.75rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.08));
        }

        .brand-header h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
        }

        .brand-header p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        .info-pill {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: #1e40af;
            line-height: 1.45;
        }

        .input-group-custom {
            margin-bottom: 1.15rem;
            position: relative;
        }

        .form-label-custom {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
        }

        .form-control-modern {
            width: 100%;
            height: 46px;
            padding: 0.625rem 1rem 0.625rem 2.6rem;
            font-size: 0.9rem;
            color: #1e293b;
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .form-control-modern:focus {
            background-color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            outline: none;
        }

        .form-control-modern.is-invalid {
            border-color: #ef4444;
            background-color: #fffaf0;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 36px;
            color: #94a3b8;
            font-size: 0.95rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-control-modern:focus + .input-icon,
        .input-group-custom:focus-within .input-icon {
            color: var(--primary);
        }

        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 36px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .password-toggle-btn:hover {
            color: #475569;
        }

        .btn-submit-register {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            margin-top: 0.5rem;
        }

        .btn-submit-register:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }

        .login-prompt {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .login-prompt a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }

        .footer-note {
            text-align: center;
            color: #64748b;
            font-size: 0.75rem;
            margin-top: 1.5rem;
        }

        .footer-note a {
            color: #94a3b8;
            text-decoration: none;
        }

        .footer-note a:hover {
            color: #cbd5e1;
        }
    </style>
</head>
<body>

    <!-- Ambient Glows -->
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="register-container">
        
        <div class="top-back-bar">
            <a href="{{ route('login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Halaman Masuk</span>
            </a>
            <a href="{{ route('home') }}" class="back-link">
                <i class="fas fa-external-link-alt"></i>
                <span>Showcase Publik</span>
            </a>
        </div>

        <div class="auth-card">
            <div class="auth-card-body">
                
                <div class="brand-header">
                    <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo PBL TRKJ Politala" class="brand-logo-img">
                    <h1>PBL TRKJ POLITALA</h1>
                    <p>Pendaftaran Akun Mahasiswa</p>
                </div>

                <div class="info-pill">
                    <i class="fas fa-id-card fa-lg text-primary mt-1"></i>
                    <div>
                        <strong>Verifikasi Otomatis NIM:</strong> Masukkan NIM Anda. Sistem akan mencocokkan data secara otomatis dengan <strong>Bank Data Mahasiswa</strong> yang telah diinputkan oleh Admin Prodi.
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger py-2 px-3 small rounded-lg mb-3">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.attempt') }}">
                    @csrf
                    
                    {{-- NIM (Identifikasi Bank Data) --}}
                    <div class="input-group-custom">
                        <label class="form-label-custom">NIM (Nomor Induk Mahasiswa) <span class="text-danger">*</span></label>
                        <i class="fas fa-fingerprint input-icon"></i>
                        <input 
                            type="text" 
                            name="nim" 
                            class="form-control-modern @error('nim') is-invalid @enderror" 
                            placeholder="Contoh: C030322001" 
                            value="{{ old('nim') }}" 
                            required 
                            autofocus
                        >
                    </div>

                    {{-- Email --}}
                    <div class="input-group-custom">
                        <label class="form-label-custom">Alamat Email Aktif <span class="text-danger">*</span></label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control-modern @error('email') is-invalid @enderror" 
                            placeholder="nama@politala.ac.id atau email aktif" 
                            value="{{ old('email') }}" 
                            required
                        >
                    </div>

                    {{-- No HP / WhatsApp --}}
                    <div class="input-group-custom">
                        <label class="form-label-custom">Nomor WhatsApp / HP Aktif <span class="text-danger">*</span></label>
                        <i class="fab fa-whatsapp input-icon"></i>
                        <input 
                            type="text" 
                            name="no_hp" 
                            class="form-control-modern @error('no_hp') is-invalid @enderror" 
                            placeholder="Contoh: 081234567890" 
                            value="{{ old('no_hp') }}" 
                            required
                        >
                    </div>

                    {{-- Password --}}
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="input-group-custom">
                                <label class="form-label-custom">Kata Sandi <span class="text-danger">*</span></label>
                                <i class="fas fa-lock input-icon"></i>
                                <input 
                                    type="password" 
                                    id="passwordInput"
                                    name="password" 
                                    class="form-control-modern @error('password') is-invalid @enderror" 
                                    placeholder="Min. 6 karakter" 
                                    required
                                >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group-custom">
                                <label class="form-label-custom">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                                <i class="fas fa-check-double input-icon"></i>
                                <input 
                                    type="password" 
                                    id="passwordConfirmInput"
                                    name="password_confirmation" 
                                    class="form-control-modern" 
                                    placeholder="Ulangi kata sandi" 
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-register">
                        <span>Verifikasi NIM & Daftar Akun</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <div class="login-prompt">
                    Sudah memiliki akun terdaftar? <a href="{{ route('login') }}">Masuk di sini</a>
                </div>

                <div class="mt-3 pt-3 border-top text-center text-muted" style="font-size: 0.775rem;">
                    <i class="fas fa-info-circle mr-1"></i> Catatan: Akun Dosen, Evaluator, dan Administrator didaftarkan langsung oleh Administrator Prodi TRKJ.
                </div>

            </div>
        </div>

        <p class="footer-note">
            &copy; {{ date('Y') }} TRKJ Politala. Hak Cipta Dilindungi. | Develop by Dery Yuswanto Jaya, M.Kom
        </p>

    </div>

    <!-- Scripts -->
    <script src="{{ asset('sbadmin2/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @include('sweetalert::alert')

</body>
</html>
