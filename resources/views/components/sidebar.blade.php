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

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKelompokSaya"
                    aria-expanded="false" aria-controls="collapseKelompokSaya">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Kelompok Saya</span>
                </a>
                <div id="collapseKelompokSaya" class="collapse" data-parent="#accordionSidebar">
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
                                    <i class="fas fa-user-circle mr-2 text-gray-400"></i>
                                    {{ $am->nama_mahasiswa }}
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

            <li class="nav-item">
                <a class="nav-link" href="{{ route('mahasiswa.kunjungan.index') }}">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Mitra Dikunjungi</span>
                </a>
            </li>

          
            @endif
        </ul>
           
