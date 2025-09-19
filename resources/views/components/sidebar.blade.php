        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center">
                <div class="sidebar-brand-icon">
                    <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo" style="width:40px;height:auto;">
                </div>        <div class="sidebar-brand-text mx-3">PBL<sup>TRKJ</sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                @if (Auth::user()->role == 'admin')
                <a class="nav-link" href="{{ url('admin/') }}">
                @elseif (Auth::user()->role == 'evaluator')
                <a class="nav-link" href="{{ url('evaluator/') }}">
                @else
                <a class="nav-link" href="{{ url('mahasiswa/') }}">
                @endif
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Beranda</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">
            @if (Auth::user()->role == 'admin')
            <!-- Heading -->
            <div class="sidebar-heading">
                Pusat Data
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-sitemap"></i>
                    <span>Data Master</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="{{ route('periode.index') }}">Periode</a>        
                        <a class="collapse-item" href="{{ route('kelas.index') }}">Kelas</a>
                        <a class="collapse-item" href="{{ route('user.index') }}">Pengguna</a>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('mahasiswa.index') }}">
                    <i class="fas fa-fw fa-user-graduate"></i>
                    <span>Mahasiwa</span></a>
            </li>


            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                PBL
            </div>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('kelompok.index') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Kelompok PBL</span></a>
            </li>

            <!-- Nav Item - Charts -->
            <li class="nav-item">
                <a class="nav-link" href="charts.html">
                    <i class="fas fa-fw fa-project-diagram"></i>
                    <span>Evaluasi</span></a>
            </li>


            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>




            @elseif (Auth::user()->role == 'evaluator')
            <!-- Heading -->
            <div class="sidebar-heading">
                evaluator
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-sitemap"></i>
                    <span>Data Master</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="{{ route('periode.index') }}">Periode</a>        
                        <a class="collapse-item" href="{{ route('kelas.index') }}">Kelas</a>
                        <a class="collapse-item" href="{{ route('user.index') }}">Pengguna</a>
                    </div>
                </div>
            </li>
            @elseif (Auth::user()->role == 'mahasiswa')
            <!-- Heading -->
            <div class="sidebar-heading">
                Mahasiswa
            </div>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('mahasiswa.kunjungan.index') }}">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Pendataan Mitra Dikunjungi</span>
                </a>
            </li>
            @endif
        </ul>
           
