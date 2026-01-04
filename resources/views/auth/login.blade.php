<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    
    <title>PBL TRKJ Politala</title>
    <link rel="icon" href="{{ asset('sbadmin2/img/logo-campus.png') }}" type="image/png">
    <!-- Custom fonts for this template-->
    <link href="{{asset('/')}}sbadmin2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{asset('/')}}sbadmin2/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body.bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-top: 60px;
        }
        .card {
            border-radius: 15px;
            overflow: hidden;
        }
        .bg-login-image {
            background-color: #f8f9fc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .bg-login-image img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 80%;
            max-height: 300px;
            object-fit: contain;
        }
        @media (max-width: 992px) {
            body.bg-gradient-primary {
                padding-top: 40px;
            }
            .card {
                margin: 20px auto;
                max-width: 400px;
            }
        }
    </style>

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image position-relative">
                                <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" width="200" alt="logo">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h5 class="h5 text-gray-900 mb-4">PBL TRKJ <br>Politeknik Negeri Tanah Laut</h5>
                                    </div>
                                    {{-- @if ($errors->any())
                                        <div id="alert-danger" class="alert alert-danger">
                                            {{ $errors->first('login') }}
                                        </div>
                                    @endif --}}

                                    <form class="user" method="POST" action="{{ route('login.attempt') }}">
                                        @csrf
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" name="username" placeholder="Nama Pengguna" required autofocus>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" name="password" placeholder="Kata Sandi" required>
                                        </input>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck" name="remember">
                                                <label class="custom-control-label" for="customCheck">Ingat Saya</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Masuk
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a href="/kunjungan-mitra" class="btn btn-dark btn-user btn-block">
                                            Lihat Data Kunjungan Mitra 
                                        </a>
                                    </div>
                                    {{-- <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register.html">Create an Account!</a>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <x-scripts/>
@include('sweetalert::alert')
</body>

</html>

