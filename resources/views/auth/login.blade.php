<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="SIMEP Login">
    <meta name="author" content="">
    
    <title>SIMEP Politala</title>
    <link rel="icon" href="{{ asset('sbadmin2/img/logo-campus.png') }}" type="image/png">
    
    <!-- Custom fonts for this template-->
    <link href="{{asset('/')}}sbadmin2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    
    <!-- Modern Premium Font (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{asset('/')}}sbadmin2/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 15px;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05), 0 4px 10px rgba(0, 0, 0, 0.03);
            background: #ffffff;
            overflow: hidden;
        }
        .card-body {
            padding: 3rem 2.5rem;
        }
        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-logo img {
            width: 70px;
            margin-bottom: 1rem;
        }
        .brand-logo h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.2rem;
            letter-spacing: -0.02em;
        }
        .brand-logo p {
            color: #64748b;
            font-size: 0.875rem;
        }
        .form-control-user {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            height: auto;
            font-size: 0.95rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        .form-control-user:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .btn-user {
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            transition: all 0.2s;
        }
        .btn-primary.btn-user {
            background-color: #2563eb;
            border-color: #2563eb;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }
        .btn-primary.btn-user:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(37, 99, 235, 0.3);
        }
        .btn-dark.btn-user {
            background-color: #f1f5f9;
            color: #475569;
            border: none;
            box-shadow: none;
        }
        .btn-dark.btn-user:hover {
            background-color: #e2e8f0;
            color: #1e293b;
        }
        hr {
            margin: 2rem 0;
            border-top: 1px solid #e2e8f0;
        }
        .custom-control-label::before {
            border-radius: 4px;
        }
        .custom-checkbox .custom-control-input:checked~.custom-control-label::before {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        /* Splash Screen CSS */
        .splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        .splash-screen.active {
            opacity: 1;
            visibility: visible;
        }
        .splash-content {
            text-align: center;
            color: white;
            animation: splashPop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        .splash-logo {
            width: 100px;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
            animation: pulseLogo 2s infinite;
        }
        .splash-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }
        .splash-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        .splash-loader {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            margin: 0 auto;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes pulseLogo {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        @keyframes splashPop {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>

<body>

    <!-- Splash Screen Overlay -->
    <div id="splash-screen" class="splash-screen active">
        <div class="splash-content">
            <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="SIMEP Logo" class="splash-logo">
            <h2 class="splash-title">SIMEP</h2>
            <p class="splash-subtitle" id="splash-text">Memuat Sistem...</p>
            <div class="splash-loader"></div>
        </div>
    </div>

    <div class="login-wrapper">
        <div class="card">
            <div class="card-body">
                <div class="brand-logo">
                    <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo SIMEP">
                    <h1>SIMEP</h1>
                    <p>Politeknik Negeri Tanah Laut</p>
                </div>

                <form class="user" method="POST" action="{{ route('login.attempt') }}">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-gray-700 mb-1" style="color: #475569">Nama Pengguna</label>
                        <input type="text" class="form-control form-control-user" name="username" placeholder="Masukkan nama pengguna" required autofocus>
                    </div>
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-gray-700 mb-1" style="color: #475569">Kata Sandi</label>
                        <input type="password" class="form-control form-control-user" name="password" placeholder="Masukkan kata sandi" required>
                    </div>
                    <div class="form-group mb-4">
                        <div class="custom-control custom-checkbox small">
                            <input type="checkbox" class="custom-control-input" id="customCheck" name="remember">
                            <label class="custom-control-label pt-1" for="customCheck" style="color: #64748b">Ingat sesi saya</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-user btn-block mt-4">
                        Masuk
                    </button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <a href="/kunjungan-mitra" class="btn btn-dark btn-user btn-block">
                        <i class="fas fa-building mr-2"></i> Akses Publik Mitra
                    </a>
                </div>
            </div>
        </div>
    </div>

    <x-scripts/>
    @include('sweetalert::alert')
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Hilangkan splash screen setelah 1 detik halaman dimuat
            setTimeout(function() {
                var splash = document.getElementById('splash-screen');
                if (splash) {
                    splash.classList.remove('active');
                }
            }, 1000);

            // Tampilkan splash screen saat form login disubmit
            var loginForm = document.querySelector('form.user');
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    var splash = document.getElementById('splash-screen');
                    var splashText = document.getElementById('splash-text');
                    if (splash && splashText) {
                        splashText.innerText = 'Memproses Otentikasi...';
                        splash.classList.add('active');
                    }
                });
            }
        });
    </script>
</body>

</html>

