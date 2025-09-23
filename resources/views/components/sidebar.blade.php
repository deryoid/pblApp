<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    {{-- Sidebar - Brand --}}
    @php $role = Auth::user()->role ?? 'mahasiswa'; @endphp
    <a class="sidebar-brand d-flex align-items-center justify-content-center"
       href="{{ $role==='admin' ? url('admin/') : ($role==='evaluator' ? url('evaluator/') : url('mahasiswa/')) }}">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo" style="width:40px;height:auto;">
        </div>
        <div class="sidebar-brand-text mx-3">PBL<sup>TRKJ</sup></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    {{-- Beranda --}}
    <li class="nav-item {{ request()->is($role) || request()->is($role.'/*') && request()->routeIs($role.'.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ $role==='admin' ? url('admin/') : ($role==='evaluator' ? url('evaluator/') : url('mahasiswa/')) }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Beranda</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    {{-- ======================= ADMIN ======================= --}}
    @if ($role === 'admin')
        <div class="sidebar-heading">Pusat Data</div>

        {{-- Data Master --}}
        @php $isMaster = request()->routeIs('periode.*','kelas.*','user.*'); @endphp
        <li class="nav-item {{ $isMaster ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMasterAdmin"
               aria-expanded="{{ $isMaster ? 'true' : 'false' }}" aria-controls="collapseMasterAdmin">
                <i class="fas fa-fw fa-sitemap"></i>
                <span>Data Master</span>
            </a>
            <div id="collapseMasterAdmin" class="collapse {{ $isMaster ? 'show' : '' }}" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
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
        <div class="sidebar-heading">PBL</div>

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
                <span>Evaluasi</span>
            </a>
        </li>

        {{-- Pengaturan Evaluasi --}}
        <li class="nav-item {{ request()->routeIs('admin.evaluasi.settings','admin.evaluasi.settings.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.evaluasi.settings') }}">
                <i class="fas fa-fw fa-sliders-h"></i>
                <span>Pengaturan Evaluasi</span>
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
        <div class="sidebar-heading">Evaluator</div>

        {{-- (Opsional) Master ringan untuk evaluator — hapus jika tak perlu --}}
        @php $isMasterEval = request()->routeIs('periode.*','kelas.*','user.*'); @endphp
        <li class="nav-item {{ $isMasterEval ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMasterEvaluator"
               aria-expanded="{{ $isMasterEval ? 'true' : 'false' }}" aria-controls="collapseMasterEvaluator">
                <i class="fas fa-fw fa-sitemap"></i>
                <span>Data Master</span>
            </a>
            <div id="collapseMasterEvaluator" class="collapse {{ $isMasterEval ? 'show' : '' }}" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item {{ request()->routeIs('periode.*') ? 'active' : '' }}" href="{{ route('periode.index') }}">Periode</a>
                    <a class="collapse-item {{ request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('kelas.index') }}">Kelas</a>
                    <a class="collapse-item {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}">Pengguna</a>
                </div>
            </div>
        </li>

        {{-- Evaluasi untuk Evaluator --}}
        <li class="nav-item {{ request()->routeIs('evaluator.evaluasi.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('evaluator.evaluasi.index') }}">
                <i class="fas fa-fw fa-clipboard-check"></i>
                <span>Evaluasi</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    {{-- =================== MAHASISWA =================== --}}
    @else
        <div class="sidebar-heading">Mahasiswa</div>

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
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($kelompokSaya)
                        <h6 class="collapse-header">
                            {{ $kelompokSaya->nama_kelompok }}
                            @if($periodeAktif)
                                <small class="text-muted"> ({{ $periodeAktif->periode }})</small>
                            @endif
                        </h6>
                        @forelse($anggotaKelompok as $am)
                            <span class="collapse-item d-flex align-items-center">
                                <img src="{{ ($am->user && $am->user->profile_photo_data_url) ? $am->user->profile_photo_data_url : asset('sbadmin2/img/undraw_profile.svg') }}"
                                     alt="Foto {{ $am->nama_mahasiswa ?? $am->nama }}" class="rounded-circle mr-2"
                                     style="width:24px;height:24px;object-fit:cover;">
                                <span>{{ $am->nama_mahasiswa ?? $am->nama }}</span>
                                @php $r = strtolower($am->pivot->role ?? ''); @endphp
                                @if($r === 'ketua')
                                    <span class="badge badge-primary ml-auto">Ketua</span>
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
        <div class="sidebar-heading">PBL</div>

        {{-- Proyek --}}
        <li class="nav-item {{ request()->routeIs('proyek.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('proyek.index') }}">
                <i class="fas fa-fw fa-project-diagram"></i>
                <span>Proyek</span>
            </a>
        </li>

        {{-- Aktivitas --}}
        <li class="nav-item {{ request()->routeIs('aktivitas.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('aktivitas.index') }}">
                <i class="fas fa-fw fa-calendar"></i>
                <span>Aktivitas</span>
            </a>
        </li>
    @endif

</ul>
