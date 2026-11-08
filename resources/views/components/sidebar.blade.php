<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    @php
        $role = Auth::user()->role ?? 'mahasiswa';
        $sidebarUser = Auth::user();
        $periodeAktif = \App\Models\Periode::getLatestActive();

        $roleBadgeMap = [
            'admin' => ['label' => 'Administrator', 'bg' => 'rgba(99, 102, 241, 0.15)', 'border' => 'rgba(129, 140, 248, 0.35)', 'text' => '#a5b4fc', 'icon' => 'fa-shield-alt'],
            'evaluator' => ['label' => 'Dosen Evaluator', 'bg' => 'rgba(245, 158, 11, 0.15)', 'border' => 'rgba(251, 191, 36, 0.35)', 'text' => '#fcd34d', 'icon' => 'fa-chalkboard-teacher'],
            'mahasiswa' => ['label' => 'Mahasiswa', 'bg' => 'rgba(16, 185, 129, 0.15)', 'border' => 'rgba(52, 211, 153, 0.35)', 'text' => '#6ee7b7', 'icon' => 'fa-user-graduate'],
        ];
        $badge = $roleBadgeMap[$role] ?? $roleBadgeMap['mahasiswa'];
        $dashboardUrl = $role === 'admin' ? url('admin/') : ($role === 'evaluator' ? url('evaluator/') : url('mahasiswa/'));
    @endphp

    {{-- Sidebar - Brand Header --}}
    <div class="sidebar-brand-wrapper">
        <a class="sidebar-brand" href="{{ $dashboardUrl }}" title="PBL TRKJ - Politeknik Negeri Tanah Laut">
            <div class="sidebar-brand-icon">
                <div class="brand-logo-tile">
                    <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo Politeknik Negeri Tanah Laut" width="28" height="28" style="width:28px;height:auto;object-fit:contain;">
                </div>
            </div>
            <div class="brand-text-group">
                <span class="brand-name">PBL TRKJ</span>
                <span class="brand-tagline">Politala</span>
            </div>
        </a>
    </div>

    {{-- Sidebar - User Profile Mini-Card --}}
    @auth
    <div class="sidebar-user-card mx-3 my-2.5 p-2 d-flex align-items-center">
        <div class="sidebar-user-avatar-wrap">
            <img src="{{ $sidebarUser->profile_photo_data_url ?? asset('sbadmin2/img/avatar-default.svg') }}"
                 alt="{{ $sidebarUser->nama_user }}"
                 width="34" height="34">
            <span class="sidebar-user-status-dot" title="Status Online"></span>
        </div>
        <div class="sidebar-user-meta overflow-hidden">
            <div class="sidebar-user-name" title="{{ $sidebarUser->nama_user }}">
                {{ $sidebarUser->nama_user }}
            </div>
            <span class="sidebar-role-badge" style="background: {{ $badge['bg'] }}; border: 1px solid {{ $badge['border'] }}; color: {{ $badge['text'] }};">
                <i class="fas {{ $badge['icon'] }} mr-1"></i>{{ $badge['label'] }}
            </span>
        </div>
    </div>
    @endauth

    {{-- ======================= UTAMA ======================= --}}
    <div class="sidebar-heading">Utama</div>

    {{-- Beranda Dashboard --}}
    @php
        $isDashboardActive = request()->is($role) || (request()->is($role.'/*') && request()->routeIs($role.'.dashboard'));
    @endphp
    <li class="nav-item {{ $isDashboardActive ? 'active' : '' }}">
        <a class="nav-link" href="{{ $dashboardUrl }}">
            <span class="nav-icon-box"><i class="fas fa-fw fa-tachometer-alt"></i></span>
            <span class="nav-link-text">Beranda</span>
        </a>
    </li>

    {{-- Showcase Publik Proyek --}}
    <li class="nav-item">
        <a class="nav-link" href="{{ route('home') }}" target="_blank">
            <span class="nav-icon-box"><i class="fas fa-fw fa-globe text-info"></i></span>
            <span class="nav-link-text">Showcase Publik</span>
            <span class="badge badge-info ml-auto font-weight-bold" style="font-size: 0.65rem; padding: 2px 6px;">SPA</span>
        </a>
    </li>

    {{-- ======================= ADMIN ======================= --}}
    @if ($role === 'admin')
        <div class="sidebar-heading">Data Akademik</div>

        {{-- Data Master Dropdown --}}
        @php $isMaster = request()->routeIs('periode.*','kelas.*','user.*','matakuliah.*'); @endphp
        <li class="nav-item {{ $isMaster ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMasterAdmin"
               aria-expanded="{{ $isMaster ? 'true' : 'false' }}" aria-controls="collapseMasterAdmin">
                <span class="nav-icon-box"><i class="fas fa-fw fa-sitemap"></i></span>
                <span class="nav-link-text">Data Master</span>
            </a>
            <div id="collapseMasterAdmin" class="collapse {{ $isMaster ? 'show' : '' }}" data-parent="#accordionSidebar">
                <div class="collapse-inner">
                    <a class="collapse-item {{ request()->routeIs('periode.*') ? 'active' : '' }}" href="{{ route('periode.index') }}">
                        <i class="fas fa-calendar-alt mr-2 text-muted" style="font-size: 0.75rem;"></i>Periode PBL
                    </a>
                    <a class="collapse-item {{ request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('kelas.index') }}">
                        <i class="fas fa-chalkboard mr-2 text-muted" style="font-size: 0.75rem;"></i>Data Kelas
                    </a>
                    <a class="collapse-item {{ request()->routeIs('matakuliah.*') ? 'active' : '' }}" href="{{ route('matakuliah.index') }}">
                        <i class="fas fa-book mr-2 text-muted" style="font-size: 0.75rem;"></i>Mata Kuliah
                    </a>
                    <a class="collapse-item {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}">
                        <i class="fas fa-user-shield mr-2 text-muted" style="font-size: 0.75rem;"></i>Akun Pengguna
                    </a>
                </div>
            </div>
        </li>

        {{-- Bank Data Mahasiswa --}}
        <li class="nav-item {{ request()->routeIs('mahasiswa.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('mahasiswa.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-user-graduate"></i></span>
                <span class="nav-link-text">Bank Data Mahasiswa</span>
            </a>
        </li>

        {{-- Bank Data Dosen --}}
        <li class="nav-item {{ request()->routeIs('dosen.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dosen.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-chalkboard-teacher"></i></span>
                <span class="nav-link-text">Bank Data Dosen</span>
            </a>
        </li>

        <div class="sidebar-heading">Manajemen PBL</div>

        {{-- Pengaturan Evaluasi --}}
        <li class="nav-item {{ request()->routeIs('admin.evaluasi.settings','admin.evaluasi.settings.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.evaluasi.settings') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-sliders-h"></i></span>
                <span class="nav-link-text">Pengaturan Evaluasi</span>
            </a>
        </li>

        {{-- Rubrik Penilaian Dinamis --}}
        <li class="nav-item {{ request()->routeIs('rubrik.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('rubrik.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-clipboard-check"></i></span>
                <span class="nav-link-text">Rubrik Penilaian</span>
            </a>
        </li>

        {{-- Kelompok PBL --}}
        <li class="nav-item {{ request()->routeIs('kelompok.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('kelompok.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-users"></i></span>
                <span class="nav-link-text">Kelompok PBL</span>
            </a>
        </li>

        <div class="sidebar-heading">Evaluasi & Nilai</div>

        {{-- Evaluasi Proyek --}}
        <li class="nav-item {{ request()->routeIs('admin.evaluasi.index','admin.evaluasi.kelompok.show') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.evaluasi.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-project-diagram"></i></span>
                <span class="nav-link-text">Evaluasi Proyek</span>
            </a>
        </li>

        {{-- Rekap Nilai --}}
        <li class="nav-item {{ request()->routeIs('admin.nilai.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.nilai.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-chart-bar"></i></span>
                <span class="nav-link-text">Rekap Nilai</span>
            </a>
        </li>

        {{-- Riwayat PBL Mahasiswa --}}
        <li class="nav-item {{ request()->routeIs('admin.riwayat.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.riwayat.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-history"></i></span>
                <span class="nav-link-text">Riwayat PBL Mahasiswa</span>
            </a>
        </li>

    {{-- =================== EVALUATOR =================== --}}
    @elseif ($role === 'evaluator')
        <div class="sidebar-heading">Data Kelompok</div>

        {{-- Kelompok PBL --}}
        <li class="nav-item {{ request()->routeIs('evaluator.kelompok.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('evaluator.kelompok.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-users"></i></span>
                <span class="nav-link-text">Kelompok PBL</span>
            </a>
        </li>

        <div class="sidebar-heading">Penilaian & Evaluasi</div>

        {{-- Evaluasi Proyek --}}
        <li class="nav-item {{ request()->routeIs('evaluator.evaluasi.index','evaluator.evaluasi.kelompok.show','evaluator.evaluasi.nilai-final') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('evaluator.evaluasi.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-project-diagram"></i></span>
                <span class="nav-link-text">Evaluasi Proyek</span>
            </a>
        </li>

        {{-- Rubrik Penilaian --}}
        <li class="nav-item {{ request()->routeIs('evaluator.evaluasi.rubrik.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('evaluator.evaluasi.rubrik.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-clipboard-check"></i></span>
                <span class="nav-link-text">Rubrik Penilaian</span>
            </a>
        </li>

        {{-- Nilai Akhir --}}
        <li class="nav-item {{ request()->routeIs('evaluator.nilai.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('evaluator.nilai.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-chart-bar"></i></span>
                <span class="nav-link-text">Nilai Akhir</span>
            </a>
        </li>

        {{-- Riwayat PBL Mahasiswa --}}
        <li class="nav-item {{ request()->routeIs('evaluator.riwayat.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('evaluator.riwayat.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-history"></i></span>
                <span class="nav-link-text">Riwayat PBL Mahasiswa</span>
            </a>
        </li>

    {{-- =================== MAHASISWA =================== --}}
    @else
        <div class="sidebar-heading">Kolaborasi</div>

        {{-- Kelompok Saya (Periode Aktif/Terbaru) --}}
        @php
            $mhs = \App\Models\Mahasiswa::where('user_id', Auth::id())->first();
            $kelompokSaya = null;
            $anggotaKelompok = collect();
            if ($mhs && $periodeAktif) {
                $kelompokSaya = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();
                if ($kelompokSaya) {
                    $anggotaKelompok = $kelompokSaya->mahasiswas()
                        ->wherePivot('periode_id', $periodeAktif->id)
                        ->with('user')
                        ->get();
                }
            }
            $isKelompokSaya = request()->routeIs('mahasiswa.kelompok.*');
        @endphp

        <li class="nav-item {{ $isKelompokSaya ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKelompokSaya"
               aria-expanded="{{ $isKelompokSaya ? 'true' : 'false' }}" aria-controls="collapseKelompokSaya">
                <span class="nav-icon-box"><i class="fas fa-fw fa-users"></i></span>
                <span class="nav-link-text">Kelompok Saya</span>
            </a>
            <div id="collapseKelompokSaya" class="collapse {{ $isKelompokSaya ? 'show' : '' }}" data-parent="#accordionSidebar">
                <div class="collapse-inner" style="max-width:100%; overflow:hidden;">
                    @if($kelompokSaya)
                        <div class="px-2 py-1.5 mb-2 rounded" style="background: rgba(37, 99, 235, 0.12); border: 1px solid rgba(96, 165, 250, 0.25);">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-white font-weight-bold text-truncate" style="font-size: 0.775rem;">
                                    <i class="fas fa-users text-primary mr-1"></i>{{ $kelompokSaya->nama_kelompok }}
                                </span>
                                @if($periodeAktif)
                                    <span class="badge badge-primary px-1.5 py-0.5" style="font-size: 0.6rem;">{{ $periodeAktif->periode }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="sidebar-member-list">
                            @forelse($anggotaKelompok as $am)
                                <div class="collapse-item d-flex align-items-center text-truncate py-1 px-2" title="{{ $am->nama_mahasiswa ?? $am->nama }}">
                                    <img src="{{ ($am->user && $am->user->profile_photo_data_url) ? $am->user->profile_photo_data_url : asset('sbadmin2/img/avatar-default.svg') }}"
                                         alt="Foto {{ $am->nama_mahasiswa ?? $am->nama }}" class="rounded-circle mr-2 flex-shrink-0"
                                         width="22" height="22"
                                         style="width:22px;height:22px;object-fit:cover;border:1px solid rgba(255,255,255,0.2);">
                                    <span class="text-truncate" style="font-size:0.775rem; color:#cbd5e1;">{{ $am->nama_mahasiswa ?? $am->nama }}</span>
                                    @php $r = strtolower($am->pivot->role ?? ''); @endphp
                                    @if($r === 'ketua')
                                        <span class="badge badge-primary ml-auto flex-shrink-0" style="font-size: 0.625rem; padding: 2px 5px;">Ketua</span>
                                    @endif
                                </div>
                            @empty
                                <span class="collapse-item text-muted" style="font-size: 0.75rem;">Belum ada anggota.</span>
                            @endforelse
                        </div>
                        <div class="px-1 pt-2 border-top mt-2" style="border-color: rgba(255,255,255,0.08) !important;">
                            <a class="btn btn-sm btn-primary btn-block py-1 text-white font-weight-bold d-flex align-items-center justify-content-center" href="{{ route('mahasiswa.kelompok.index') }}" style="font-size:0.75rem; border-radius: 8px;">
                                <i class="fas fa-users-cog mr-1.5"></i> Kelola & Undang
                            </a>
                        </div>
                    @else
                        <div class="p-2 text-center rounded mb-2" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.12);">
                            <i class="fas fa-user-friends text-muted mb-1" style="font-size: 1.15rem;"></i>
                            <div class="text-muted small" style="font-size: 0.75rem;">Belum tergabung kelompok</div>
                        </div>
                        <div class="px-1">
                            <a class="btn btn-sm btn-primary btn-block py-1 text-white font-weight-bold d-flex align-items-center justify-content-center" href="{{ route('mahasiswa.kelompok.index') }}" style="font-size:0.75rem; border-radius: 8px;">
                                <i class="fas fa-plus-circle mr-1.5"></i> Bentuk Kelompok
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </li>

        {{-- Mitra Dikunjungi --}}
        <li class="nav-item {{ request()->routeIs('mahasiswa.kunjungan.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('mahasiswa.kunjungan.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-building"></i></span>
                <span class="nav-link-text">Mitra Dikunjungi</span>
            </a>
        </li>

        <div class="sidebar-heading">Proyek & Kegiatan</div>

        {{-- Proyek --}}
        <li class="nav-item {{ request()->routeIs('proyek.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('proyek.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-project-diagram"></i></span>
                <span class="nav-link-text">Papan Proyek PBL</span>
            </a>
        </li>

        {{-- Aktivitas --}}
        <li class="nav-item {{ request()->routeIs('aktivitas.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('aktivitas.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-calendar-check"></i></span>
                <span class="nav-link-text">Aktivitas & Logbook</span>
            </a>
        </li>

        <div class="sidebar-heading">Capaian Akademik</div>

        {{-- Nilai --}}
        <li class="nav-item {{ request()->routeIs('mahasiswa.nilai.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('mahasiswa.nilai.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-chart-bar"></i></span>
                <span class="nav-link-text">Transkrip Nilai</span>
            </a>
        </li>

        {{-- Riwayat PBL --}}
        <li class="nav-item {{ request()->routeIs('mahasiswa.riwayat.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('mahasiswa.riwayat.index') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-history"></i></span>
                <span class="nav-link-text">Riwayat PBL Saya</span>
            </a>
        </li>

        <div class="sidebar-heading">Akun</div>

        {{-- Biodata & Profil --}}
        <li class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <span class="nav-icon-box"><i class="fas fa-fw fa-user-cog"></i></span>
                <span class="nav-link-text">Biodata & Profil</span>
            </a>
        </li>
    @endif

    {{-- Active Period Footer Card --}}
    @if($periodeAktif)
        <div class="sidebar-period-card mx-3 mt-auto mb-2 p-2.5 rounded-xl">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="period-label">
                    <i class="fas fa-calendar-check mr-1 text-info"></i> Periode PBL
                </span>
                <span class="period-badge">
                    <span class="status-pulse-dot mr-1"></span>Aktif
                </span>
            </div>
            <div class="period-name text-truncate">
                {{ $periodeAktif->periode }}
            </div>
            @if($periodeAktif->keterangan)
                <div class="period-desc text-truncate">
                    {{ $periodeAktif->keterangan }}
                </div>
            @endif
        </div>
    @endif

    {{-- Sidebar Toggler (Sidebar) --}}
    <div class="sidebar-toggle-wrapper text-center d-none d-md-block mb-3 mt-1">
        <button class="sidebar-toggler-btn border-0" id="sidebarToggle" title="Perluas / Ciutkan Navigasi" aria-label="Toggle navigasi sidebar">
            <i class="fas fa-chevron-left toggler-icon"></i>
        </button>
    </div>

</ul>
