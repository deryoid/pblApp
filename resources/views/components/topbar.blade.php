<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm px-2 px-sm-4">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-2 mr-sm-3" style="color: #64748b;">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Left breadcrumb / Quick Brand -->
    <div class="d-none d-sm-flex align-items-center">
        <span class="badge badge-primary mr-2">
            <i class="fas fa-layer-group mr-1"></i> SIMEP PBL
        </span>
        <span class="text-muted small font-weight-bold">
            Politeknik Negeri Tanah Laut
        </span>
    </div>

    <!-- Topbar Navbar (Right Items) -->
    <ul class="navbar-nav ml-auto align-items-center">

        <!-- Quick Access to Public Showcase -->
        <li class="nav-item mr-2 mr-sm-3">
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-primary" target="_blank" title="Buka Galeri Proyek Selesai di Tab Baru">
                <i class="fas fa-external-link-alt mr-1"></i>
                <span class="d-none d-md-inline">Showcase Proyek</span>
            </a>
        </li>

        <!-- Divider -->
        <div class="topbar-divider d-none d-sm-block" style="border-right: 1px solid #e2e8f0; height: 2rem; margin: 0 0.75rem;"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle d-flex align-items-center p-2 rounded" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border: 1px solid transparent;">
                @auth
                    <img class="img-profile rounded-circle mr-2" alt="Avatar"
                         src="{{ Auth::user()->profile_photo_data_url ?? asset('sbadmin2/img/undraw_profile.svg') }}"
                         style="width:36px;height:36px;object-fit:cover;border:2px solid #2563eb;">
                    <div class="d-none d-lg-block text-left mr-2">
                        <div class="font-weight-bold text-dark" style="font-size: 0.875rem; line-height: 1.2;">
                            {{ Auth::user()->nama_user }}
                        </div>
                        <div class="text-muted small" style="font-size: 0.75rem; text-transform: uppercase;">
                            {{ Auth::user()->role }}
                        </div>
                    </div>
                    <i class="fas fa-chevron-down text-muted small ml-1"></i>
                @endauth 
            </a>

            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in border-0 rounded-lg p-2"
                aria-labelledby="userDropdown" style="min-width: 220px; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;">
                <div class="px-3 py-2 border-bottom mb-2">
                    <p class="font-weight-bold text-dark mb-0 small">{{ Auth::user()->nama_user }}</p>
                    <p class="text-muted small mb-0">{{ Auth::user()->email ?? Auth::user()->username }}</p>
                </div>
                <a class="dropdown-item rounded py-2 d-flex align-items-center" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user-circle fa-sm fa-fw mr-2 text-primary"></i>
                    <span>Pengaturan Profil</span>
                </a>
                <a class="dropdown-item rounded py-2 d-flex align-items-center" href="{{ route('home') }}" target="_blank">
                    <i class="fas fa-star fa-sm fa-fw mr-2 text-warning"></i>
                    <span>Showcase Proyek SPA</span>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item rounded py-2 d-flex align-items-center text-danger" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                    <span>Keluar Sistem</span>
                </a>
            </div>
        </li>

    </ul>

</nav>
