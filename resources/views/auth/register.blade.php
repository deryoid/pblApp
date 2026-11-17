<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Pendaftaran Akun Pengguna (Mahasiswa & Dosen) - PBL TRKJ Politala">
    <meta name="author" content="Dery Yuswanto Jaya, M.Kom - TRKJ Politala">
    
    <title>Pendaftaran Akun | PBL TRKJ Politala</title>
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
            max-width: 560px;
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
            margin-bottom: 1.25rem;
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

        /* Role Switcher Tabs */
        .role-switcher-box {
            background: #f1f5f9;
            padding: 5px;
            border-radius: 14px;
            display: flex;
            gap: 6px;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }

        .role-tab-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1rem;
            border: none;
            background: transparent;
            color: #64748b;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .role-tab-btn:hover {
            color: #1e293b;
        }

        .role-tab-btn.active {
            background: #ffffff;
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12), 0 2px 4px rgba(0, 0, 0, 0.04);
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
                    <p id="registerSubTitle">
                        {{ ($activeRole ?? 'mahasiswa') === 'dosen' ? 'Pendaftaran Akun Dosen / Pengajar' : 'Pendaftaran Akun Mahasiswa' }}
                    </p>
                </div>

                {{-- Role Switcher Tabs --}}
                <div class="role-switcher-box">
                    <button type="button" id="tabMahasiswaBtn" class="role-tab-btn {{ ($activeRole ?? 'mahasiswa') !== 'dosen' ? 'active' : '' }}" onclick="switchRole('mahasiswa')">
                        <i class="fas fa-user-graduate"></i>
                        <span>Mahasiswa</span>
                    </button>
                    <button type="button" id="tabDosenBtn" class="role-tab-btn {{ ($activeRole ?? 'mahasiswa') === 'dosen' ? 'active' : '' }}" onclick="switchRole('dosen')">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Dosen / Pengajar</span>
                    </button>
                </div>

                {{-- Solusi Terbaik Jika Akun Sudah Terdaftar / Duplikat Ditemukan --}}
                @if(session('existing_user_resolution'))
                    @php
                        $res = session('existing_user_resolution');
                    @endphp
                    <div class="alert border-0 shadow-sm rounded-lg mb-4 p-3" style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
                        <div class="d-flex align-items-start gap-3">
                            <div style="background: #fef3c7; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-user-check text-warning" style="font-size: 1.25rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.95rem;">
                                    {{ $res['title'] ?? 'Akun Sudah Terdaftar' }}
                                </h6>
                                <p class="text-secondary mb-2" style="font-size: 0.825rem; line-height: 1.45;">
                                    {{ $res['message'] }}
                                </p>
                                <div class="d-flex flex-wrap align-items-center gap-2 pt-1">
                                    <a href="{{ route('login', ['login' => $res['login_key'] ?? '']) }}" class="btn btn-primary btn-sm font-weight-bold px-3 py-2 text-white shadow-sm" style="border-radius: 8px; font-size: 0.8125rem;">
                                        <i class="fas fa-sign-in-alt mr-1"></i> Masuk Sekarang ke Akun Anda
                                    </a>
                                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm px-3 py-2" style="border-radius: 8px; font-size: 0.8125rem;">
                                        <i class="fas fa-key mr-1"></i> Halaman Masuk
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errors->any() && !session('existing_user_resolution'))
                    <div class="alert alert-danger py-2 px-3 small rounded-lg mb-3">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- PANEL FORM MAHASISWA --}}
                <div id="paneMahasiswa" style="{{ ($activeRole ?? 'mahasiswa') === 'dosen' ? 'display: none;' : 'display: block;' }}">
                    <div class="info-pill">
                        <i class="fas fa-id-card fa-lg text-primary mt-1"></i>
                        <div>
                            <strong>Verifikasi Otomatis NIM:</strong> Masukkan NIM Anda. Sistem akan mencocokkan data secara otomatis dengan <strong>Bank Data Mahasiswa</strong> yang telah diinputkan oleh Admin Prodi.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('register.attempt') }}">
                        @csrf
                        <input type="hidden" name="role" value="mahasiswa">
                        
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
                                        id="mhsPasswordInput"
                                        name="password" 
                                        class="form-control-modern @error('password') is-invalid @enderror" 
                                        placeholder="Min. 6 karakter" 
                                        required
                                    >
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('mhsPasswordInput', 'mhsPasswordEye')">
                                        <i id="mhsPasswordEye" class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <label class="form-label-custom">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                                    <i class="fas fa-check-double input-icon"></i>
                                    <input 
                                        type="password" 
                                        id="mhsPasswordConfirmInput"
                                        name="password_confirmation" 
                                        class="form-control-modern" 
                                        placeholder="Ulangi kata sandi" 
                                        required
                                    >
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('mhsPasswordConfirmInput', 'mhsConfirmEye')">
                                        <i id="mhsConfirmEye" class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit-register">
                            <span>Verifikasi NIM & Daftar Akun</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>

                {{-- PANEL FORM DOSEN --}}
                <div id="paneDosen" style="{{ ($activeRole ?? 'mahasiswa') === 'dosen' ? 'display: block;' : 'display: none;' }}">
                    <div class="info-pill" style="background: #f0fdf4; border-color: #bbf7d0; color: #166534;">
                        <i class="fas fa-chalkboard-teacher fa-lg text-success mt-1"></i>
                        <div>
                            <strong>Verifikasi & Sinkronisasi Dosen:</strong> Masukkan NIP atau NIDN Anda. Sistem akan menyinkronkan data profil Anda secara otomatis dengan <strong>Bank Data Dosen</strong> Prodi TRKJ.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('register.dosen.attempt') }}">
                        @csrf
                        <input type="hidden" name="role" value="dosen">
                        
                        {{-- NIP / NIDN --}}
                        <div class="input-group-custom">
                            <label class="form-label-custom">NIP (Nomor Induk Pegawai) / NIDN <span class="text-danger">*</span></label>
                            <i class="fas fa-id-badge input-icon"></i>
                            <input 
                                type="text" 
                                name="nip" 
                                class="form-control-modern @error('nip') is-invalid @enderror" 
                                placeholder="Contoh: 198501012010121001 atau NIDN" 
                                value="{{ old('nip') }}" 
                                required
                            >
                        </div>

                        {{-- Nama Dosen & Gelar --}}
                        <div class="input-group-custom">
                            <label class="form-label-custom">Nama Lengkap & Gelar Dosen <span class="text-danger">*</span></label>
                            <i class="fas fa-user-tie input-icon"></i>
                            <input 
                                type="text" 
                                name="nama_user" 
                                class="form-control-modern @error('nama_user') is-invalid @enderror" 
                                placeholder="Contoh: Dr. Ahmad Fauzi, M.Kom." 
                                value="{{ old('nama_user') }}" 
                                required
                            >
                        </div>

                        {{-- Email --}}
                        <div class="input-group-custom">
                            <label class="form-label-custom">Alamat Email Aktif (Kampus / Pribadi) <span class="text-danger">*</span></label>
                            <i class="fas fa-envelope input-icon"></i>
                            <input 
                                type="email" 
                                name="email" 
                                class="form-control-modern @error('email') is-invalid @enderror" 
                                placeholder="nama.dosen@politala.ac.id" 
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

                        {{-- Bidang Keahlian (Opsional) --}}
                        <div class="input-group-custom">
                            <label class="form-label-custom">Bidang Keahlian / Kepakaran <span class="text-muted">(Opsional)</span></label>
                            <i class="fas fa-laptop-code input-icon"></i>
                            <input 
                                type="text" 
                                name="keahlian" 
                                class="form-control-modern @error('keahlian') is-invalid @enderror" 
                                placeholder="Contoh: Rekayasa Perangkat Lunak, Jaringan Komputer, IoT" 
                                value="{{ old('keahlian') }}"
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
                                        id="dosenPasswordInput"
                                        name="password" 
                                        class="form-control-modern @error('password') is-invalid @enderror" 
                                        placeholder="Min. 6 karakter" 
                                        required
                                    >
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('dosenPasswordInput', 'dosenPasswordEye')">
                                        <i id="dosenPasswordEye" class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <label class="form-label-custom">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                                    <i class="fas fa-check-double input-icon"></i>
                                    <input 
                                        type="password" 
                                        id="dosenPasswordConfirmInput"
                                        name="password_confirmation" 
                                        class="form-control-modern" 
                                        placeholder="Ulangi kata sandi" 
                                        required
                                    >
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('dosenPasswordConfirmInput', 'dosenConfirmEye')">
                                        <i id="dosenConfirmEye" class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit-register" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                            <span>Daftar Akun Dosen</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>

                <div class="login-prompt">
                    Sudah memiliki akun terdaftar? <a href="{{ route('login') }}">Masuk di sini</a>
                </div>

                <div class="mt-3 pt-3 border-top text-center text-muted" style="font-size: 0.775rem;">
                    <i class="fas fa-info-circle mr-1"></i> Catatan: Akun Administrator sistem didaftarkan langsung oleh Administrator Prodi TRKJ.
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

    <script>
        function switchRole(role) {
            const paneMhs = document.getElementById('paneMahasiswa');
            const paneDosen = document.getElementById('paneDosen');
            const tabMhsBtn = document.getElementById('tabMahasiswaBtn');
            const tabDosenBtn = document.getElementById('tabDosenBtn');
            const subTitle = document.getElementById('registerSubTitle');

            if (role === 'dosen') {
                paneMhs.style.display = 'none';
                paneDosen.style.display = 'block';
                tabMhsBtn.classList.remove('active');
                tabDosenBtn.classList.add('active');
                subTitle.textContent = 'Pendaftaran Akun Dosen / Pengajar';
                if (window.history.replaceState) {
                    const newUrl = new URL(window.location);
                    newUrl.searchParams.set('role', 'dosen');
                    window.history.replaceState({}, '', newUrl);
                }
            } else {
                paneMhs.style.display = 'block';
                paneDosen.style.display = 'none';
                tabMhsBtn.classList.add('active');
                tabDosenBtn.classList.remove('active');
                subTitle.textContent = 'Pendaftaran Akun Mahasiswa';
                if (window.history.replaceState) {
                    const newUrl = new URL(window.location);
                    newUrl.searchParams.delete('role');
                    window.history.replaceState({}, '', newUrl);
                }
            }
        }

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
    </script>

</body>
</html>
