<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Atur Ulang Kata Sandi - PBL TRKJ Politala">
    <meta name="author" content="Dery Yuswanto Jaya, M.Kom - TRKJ Politala">
    
    <title>Atur Ulang Kata Sandi | PBL TRKJ Politala</title>
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

        .auth-container {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 10;
            margin: 0 auto;
        }

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
        }

        .back-showcase-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: translateY(-1px);
        }

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
            margin-bottom: 1.5rem;
        }

        .brand-logo-img {
            width: clamp(50px, 7vw, 60px);
            height: clamp(50px, 7vw, 60px);
            object-fit: contain;
            margin-bottom: 0.75rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.06));
        }

        .brand-header h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(1.25rem, 4vw, 1.45rem);
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.35rem;
            letter-spacing: -0.02em;
        }

        .brand-header p {
            color: #64748b;
            font-size: clamp(0.8125rem, 2.5vw, 0.875rem);
            line-height: 1.45;
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
            margin-bottom: 1.15rem;
        }

        .input-group-custom i.input-icon {
            position: absolute;
            left: 1rem;
            top: 38px;
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

        .code-input {
            letter-spacing: 0.35em;
            font-weight: 700;
            font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
            font-size: 1.35rem;
            text-align: center;
            padding-left: 1rem;
            padding-right: 1rem;
            color: #1e3a8a;
            background-color: #f8fafc;
        }

        .code-input:focus {
            background-color: #ffffff;
        }

        .password-toggle-btn {
            position: absolute;
            right: 1rem;
            top: 38px;
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

        .btn-submit-action {
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

        .btn-submit-action:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
            color: #ffffff;
        }

        .resend-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.825rem;
            color: #64748b;
        }

        .btn-resend {
            background: transparent;
            border: none;
            color: #2563eb;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
            font-size: 0.825rem;
            text-decoration: underline;
            transition: color 0.15s;
        }

        .btn-resend:hover {
            color: #1d4ed8;
        }

        .card-footer-links {
            margin-top: 1.25rem;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
        }

        .card-footer-links a {
            color: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }

        .card-footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="auth-container">
        
        <!-- Back Link -->
        <div class="top-back-bar">
            <a href="{{ route('login') }}" class="back-showcase-link">
                <i class="fas fa-arrow-left"></i>
                <span>Batal & Kembali ke Halaman Masuk</span>
            </a>
        </div>

        <div class="auth-card">
            <div class="auth-card-body">
                
                <div class="brand-header">
                    <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo PBL TRKJ Politala" class="brand-logo-img">
                    <h1>Atur Sandi Baru</h1>
                    <p>Masukkan kode verifikasi 6 digit yang dikirim ke email Anda beserta kata sandi baru.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-lg mb-3" style="background: #fef2f2; border-left: 4px solid #ef4444 !important; padding: 0.875rem 1rem;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-exclamation-circle text-danger mt-1" style="font-size: 1.1rem;"></i>
                            <div style="font-size: 0.825rem; color: #991b1b; line-height: 1.45;">
                                <strong style="color: #b91c1c;">Gagal Memperbarui Kata Sandi:</strong><br>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                    @csrf
                    
                    <div class="input-group-custom">
                        <label class="form-label-custom">Alamat Email Terdaftar</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            name="email" 
                            id="emailInput"
                            class="form-control-modern" 
                            placeholder="nama@politala.ac.id" 
                            value="{{ old('email', $email ?? request('email')) }}" 
                            required 
                            oninput="syncResendEmail(this.value)"
                        >
                    </div>

                    <div class="input-group-custom">
                        <label class="form-label-custom text-center">Kode Verifikasi (6 Digit)</label>
                        <input 
                            type="text" 
                            name="code" 
                            class="form-control-modern code-input" 
                            placeholder="000000" 
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            value="{{ old('code') }}" 
                            required 
                            autofocus
                        >
                    </div>

                    <div class="input-group-custom">
                        <label class="form-label-custom">Kata Sandi Baru</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            id="newPasswordInput"
                            name="password" 
                            class="form-control-modern" 
                            placeholder="Minimal 6 karakter" 
                            required
                        >
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('newPasswordInput', 'eyeIcon1')">
                            <i id="eyeIcon1" class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="input-group-custom">
                        <label class="form-label-custom">Konfirmasi Kata Sandi Baru</label>
                        <i class="fas fa-check-double input-icon"></i>
                        <input 
                            type="password" 
                            id="confirmPasswordInput"
                            name="password_confirmation" 
                            class="form-control-modern" 
                            placeholder="Ketik ulang kata sandi baru" 
                            required
                        >
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('confirmPasswordInput', 'eyeIcon2')">
                            <i id="eyeIcon2" class="fas fa-eye"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn-submit-action">
                        <i class="fas fa-check-circle"></i>
                        <span>Simpan & Perbarui Kata Sandi</span>
                    </button>
                </form>

                <!-- Resend Code Box -->
                <div class="resend-box">
                    <span>Tidak menerima kode verifikasi? Periksa folder Spam atau </span>
                    <form method="POST" action="{{ route('password.resend') }}" style="display: inline;" id="resendForm">
                        @csrf
                        <input type="hidden" name="email" id="resendEmailInput" value="{{ old('email', $email ?? request('email')) }}">
                        <button type="submit" class="btn-resend">
                            <i class="fas fa-redo-alt mr-1"></i>Kirim Ulang Kode
                        </button>
                    </form>
                </div>

                <div class="card-footer-links">
                    <a href="{{ route('login') }}"><i class="fas fa-arrow-left mr-1"></i>Kembali ke Halaman Masuk</a>
                </div>

            </div>
        </div>
    </div>

    <!-- SweetAlert2 Scripts -->
    @include('sweetalert::alert')

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function syncResendEmail(val) {
            const resendInput = document.getElementById('resendEmailInput');
            if (resendInput) {
                resendInput.value = val;
            }
        }
    </script>

</body>
</html>
