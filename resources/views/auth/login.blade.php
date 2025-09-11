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

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image">
                                <img src="{{ asset('sbadmin2/img/logo-pbl.png') }}" alt="logo" class="mx-auto d-block" style="width: 80%; padding-top: 50px;">
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

