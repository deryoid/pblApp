<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm px-2 px-sm-4">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-2 mr-sm-3" style="color: #64748b;" aria-label="Toggle navigasi sidebar">
        <i class="fa fa-bars" aria-hidden="true"></i>
    </button>

    <!-- Left breadcrumb / Quick Brand -->
    <div class="d-none d-sm-flex align-items-center">
        <span class="badge badge-primary mr-2">
            <i class="fas fa-layer-group mr-1"></i> PBL TRKJ Politala
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
        @auth
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img class="img-profile rounded-circle mr-2" alt="Avatar Pengguna"
                     src="{{ Auth::user()->profile_photo_data_url ?? asset('sbadmin2/img/avatar-default.svg') }}"
                     width="38" height="38"
                     style="width:38px;height:38px;object-fit:cover;border:2px solid #2563eb;box-shadow: 0 2px 6px rgba(37,99,235,0.25);">
                <div class="d-none d-sm-block text-left mr-2">
                    <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.85rem; line-height: 1.2; max-width: 140px;">
                        {{ Auth::user()->nama_user }}
                    </div>
                    <span class="badge badge-primary px-2 py-0.5 mt-0.5" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.04em;">
                        {{ Auth::user()->role }}
                    </span>
                </div>
                <i class="fas fa-chevron-down text-muted small ml-1"></i>
            </a>

            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in border-0 rounded-lg p-2"
                aria-labelledby="userDropdown">
                <div class="px-3 py-2.5 border-bottom mb-2 bg-light" style="border-radius: 8px;">
                    <div class="font-weight-bold text-dark mb-0 text-truncate" style="font-size: 0.875rem;">
                        {{ Auth::user()->nama_user }}
                    </div>
                    <div class="text-muted small text-truncate" style="font-size: 0.775rem;">
                        <i class="fas fa-envelope fa-xs mr-1 text-muted"></i>{{ Auth::user()->email ?? Auth::user()->username }}
                    </div>
                </div>
                <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user-circle fa-sm fa-fw mr-2 text-primary"></i>
                    <span>Pengaturan Profil</span>
                </a>
                <a class="dropdown-item py-2" href="{{ route('home') }}" target="_blank">
                    <i class="fas fa-globe fa-sm fa-fw mr-2 text-info"></i>
                    <span>Showcase Proyek SPA</span>
                </a>
                <div class="dropdown-divider my-1"></div>
                <a class="dropdown-item py-2 text-danger" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                    <span>Keluar Sistem</span>
                </a>
            </div>
        </li>
        @endauth

    </ul>

</nav>
