<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    {{-- Sidebar - Brand --}}
    @php $role = Auth::user()->role ?? 'mahasiswa'; @endphp
    <a class="sidebar-brand d-flex align-items-center justify-content-center"
       href="{{ $role==='admin' ? url('admin/') : ($role==='evaluator' ? url('evaluator/') : url('mahasiswa/')) }}">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo" style="width:38px;height:auto;">
        </div>
        <div class="sidebar-brand-text mx-2">SIMEP <span>PBL</span></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    {{-- Beranda Dashboard --}}
    <li class="nav-item {{ request()->is($role) || request()->is($role.'/*') && request()->routeIs($role.'.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ $role==='admin' ? url('admin/') : ($role==='evaluator' ? url('evaluator/') : url('mahasiswa/')) }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Beranda</span>
        </a>
    </li>

    {{-- Showcase Publik Proyek --}}
    <li class="nav-item">
        <a class="nav-link" href="{{ route('home') }}" target="_blank">
            <i class="fas fa-fw fa-globe text-info"></i>
            <span>Showcase Publik</span>
            <span class="badge badge-info ml-auto">SPA</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    {{-- ======================= ADMIN ======================= --}}
    @if ($role === 'admin')
        <div class="sidebar-heading">Pusat Data Master</div>

        {{-- Data Master --}}
        @php $isMaster = request()->routeIs('periode.*','kelas.*','user.*'); @endphp
        <li class="nav-item {{ $isMaster ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMasterAdmin"
               aria-expanded="{{ $isMaster ? 'true' : 'false' }}" aria-controls="collapseMasterAdmin">
                <i class="fas fa-fw fa-sitemap"></i>
                <span>Data Master</span>
            </a>
            <div id="collapseMasterAdmin" class="collapse {{ $isMaster ? 'show' : '' }}" data-parent="#accordionSidebar">
                <div class="collapse-inner">
                    <a class="collapse-item {{ request()->routeIs('periode.*') ? 'active' : '' }}" href="{{ route('periode.index') }}">Periode</a>
                    <a class="collapse-item {{ request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('kelas.index') }}">Kelas</a>
                    <a class="collapse-item {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}">Pengguna</a>
                </div>
            </div>
        </li>

        {{-- Mahasiswa --}}
        <li class="nav-item {{ request()->routeIs('mahasiswa.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('mahasiswa.index') }}">
                <i class="fas fa-fw fa-user-graduate"></i>
                <span>Mahasiswa</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Kegiatan PBL</div>

        {{-- Pengaturan Evaluasi --}}
        <li class="nav-item {{ request()->routeIs('admin.evaluasi.settings','admin.evaluasi.settings.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.evaluasi.settings') }}">
                <i class="fas fa-fw fa-sliders-h"></i>
                <span>Pengaturan Evaluasi</span>
            </a>
        </li>

        {{-- Kelompok PBL --}}
        <li class="nav-item {{ request()->routeIs('kelompok.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('kelompok.index') }}">
                <i class="fas fa-fw fa-users"></i>
                <span>Kelompok PBL</span>
            </a>
        </li>

        {{-- Evaluasi (Index) --}}
        <li class="nav-item {{ request()->routeIs('admin.evaluasi.index','admin.evaluasi.kelompok.show') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.evaluasi.index') }}">
                <i class="fas fa-fw fa-project-diagram"></i>
                <span>Evaluasi Proyek</span>
            </a>
        </li>

        {{-- Nilai --}}
        <li class="nav-item {{ request()->routeIs('admin.nilai.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.nilai.index') }}">
                <i class="fas fa-fw fa-chart-bar"></i>
                <span>Rekap Nilai</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        {{-- Sidebar Toggler --}}
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    {{-- =================== EVALUATOR =================== --}}
    @elseif ($role === 'evaluator')
        <div class="sidebar-heading">Pusat Data</div>

        {{-- Kelompok PBL --}}
        <li class="nav-item {{ request()->routeIs('evaluator.kelompok.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('evaluator.kelompok.index') }}">
                <i class="fas fa-fw fa-users"></i>
                <span>Kelompok PBL</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">
        <div class="sidebar-heading">PBL & Penilaian</div>

        {{-- Evaluasi --}}
        <li class="nav-item {{ request()->routeIs('evaluator.evaluasi.index','evaluator.evaluasi.kelompok.show','evaluator.evaluasi.nilai-final') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('evaluator.evaluasi.index') }}">
                <i class="fas fa-fw fa-project-diagram"></i>
                <span>Evaluasi Proyek</span>
            </a>
        </li>

        {{-- Nilai --}}
        <li class="nav-item {{ request()->routeIs('evaluator.nilai.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('evaluator.nilai.index') }}">
                <i class="fas fa-fw fa-chart-bar"></i>
                <span>Nilai Akhir</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    {{-- =================== MAHASISWA =================== --}}
    @else
        <div class="sidebar-heading">Kelompok Mahasiswa</div>

        {{-- Kelompok Saya (Periode Aktif) --}}
        @php
            $mhs = \App\Models\Mahasiswa::where('user_id', Auth::id())->first();
            $periodeAktif = \App\Models\Periode::where('status_periode','Aktif')->orderByDesc('created_at')->first();
            $kelompokSaya = null;
            $anggotaKelompok = collect();
            if ($mhs) {
                if ($periodeAktif) {
                    $kelompokSaya = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();
                    if ($kelompokSaya) {
                        $anggotaKelompok = $kelompokSaya->mahasiswas()
                            ->wherePivot('periode_id', $periodeAktif->id)
                            ->with('user')
                            ->get();
                    }
                }
                if (!$kelompokSaya) {
                    $kelompokSaya = $mhs->kelompoks()->latest('kelompok_mahasiswa.created_at')->first();
                    if ($kelompokSaya) {
                        $anggotaKelompok = $kelompokSaya->mahasiswas()->with('user')->get();
                    }
                }
            }
        @endphp

        @php $isKelompokSaya = request()->routeIs('mahasiswa.kelompok.*'); @endphp
        <li class="nav-item {{ $isKelompokSaya ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKelompokSaya"
               aria-expanded="{{ $isKelompokSaya ? 'true' : 'false' }}" aria-controls="collapseKelompokSaya">
                <i class="fas fa-fw fa-users"></i>
                <span>Kelompok Saya</span>
            </a>
            <div id="collapseKelompokSaya" class="collapse {{ $isKelompokSaya ? 'show' : '' }}" data-parent="#accordionSidebar">
                <div class="collapse-inner" style="max-width:100%; overflow:hidden;">
                    @if($kelompokSaya)
                        <h6 class="text-white small font-weight-bold px-2 py-1 mb-1 text-truncate">
                            {{ $kelompokSaya->nama_kelompok }}
                            @if($periodeAktif)
                                <span class="text-muted small"> ({{ $periodeAktif->periode }})</span>
                            @endif
                        </h6>
                        @forelse($anggotaKelompok as $am)
                            <span class="collapse-item d-flex align-items-center text-truncate" title="{{ $am->nama_mahasiswa ?? $am->nama }}">
                                <img src="{{ ($am->user && $am->user->profile_photo_data_url) ? $am->user->profile_photo_data_url : asset('sbadmin2/img/undraw_profile.svg') }}"
                                     alt="Foto {{ $am->nama_mahasiswa ?? $am->nama }}" class="rounded-circle mr-2 flex-shrink-0"
                                     style="width:22px;height:22px;object-fit:cover;">
                                <span class="text-truncate" style="font-size:0.8rem;">{{ $am->nama_mahasiswa ?? $am->nama }}</span>
                                @php $r = strtolower($am->pivot->role ?? ''); @endphp
                                @if($r === 'ketua')
                                    <span class="badge badge-primary ml-auto flex-shrink-0" style="font-size: 0.65rem;">Ketua</span>
                                @endif
                            </span>
                        @empty
                            <span class="collapse-item text-muted">Belum ada anggota.</span>
                        @endforelse
                    @else
                        <span class="collapse-item text-muted">Belum tergabung dalam kelompok.</span>
                    @endif
                </div>
            </div>
        </li>

        {{-- Mitra Dikunjungi --}}
        <li class="nav-item {{ request()->routeIs('mahasiswa.kunjungan.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('mahasiswa.kunjungan.index') }}">
                <i class="fas fa-fw fa-building"></i>
                <span>Mitra Dikunjungi</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Proyek & Kegiatan</div>

        {{-- Proyek --}}
        <li class="nav-item {{ request()->routeIs('proyek.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('proyek.index') }}">
                <i class="fas fa-fw fa-project-diagram"></i>
                <span>Papan Proyek PBL</span>
            </a>
        </li>

        {{-- Aktivitas --}}
        <li class="nav-item {{ request()->routeIs('aktivitas.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('aktivitas.index') }}">
                <i class="fas fa-fw fa-calendar-check"></i>
                <span>Aktivitas & Logbook</span>
            </a>
        </li>

        {{-- Nilai --}}
        <li class="nav-item {{ request()->routeIs('mahasiswa.nilai.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('mahasiswa.nilai.index') }}">
                <i class="fas fa-fw fa-chart-bar"></i>
                <span>Transkrip Nilai</span>
            </a>
        </li>
    @endif

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    {{-- Sidebar Toggler (Sidebar) --}}
    <div class="text-center d-none d-md-inline mb-4">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
