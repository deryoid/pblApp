@extends('layout.app')
@section('content')
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard PBL</h1>
                        @if($activePeriode)
                            <span class="badge badge-info">Periode Aktif: {{ $activePeriode->periode }}</span>
                        @endif
                    </div>

                    <!-- Info Periode Aktif -->
                    @if($activePeriode)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-left-info shadow h-100 py-3">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col">
                                            <div class="h5 mb-2 font-weight-bold text-gray-800">
                                                <i class="fas fa-calendar-alt mr-2"></i>Periode Aktif
                                            </div>
                                            <div class="text-gray-700">{{ $activePeriode->periode }}</div>
                                            @if($activePeriode->tanggal_mulai && $activePeriode->tanggal_selesai)
                                            <div class="small text-muted mt-1">
                                                {{ \Carbon\Carbon::parse($activePeriode->tanggal_mulai)->format('d M Y') }} -
                                                {{ \Carbon\Carbon::parse($activePeriode->tanggal_selesai)->format('d M Y') }}
                                            </div>
                                            @endif
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Belum ada periode aktif</strong> - Silakan buat periode baru untuk memulai kegiatan PBL.
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Data Akademik & Pengguna -->
                    <div class="row">
                        <!-- Total Periode -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Periode</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPeriodes }}</div>
                                            <div class="small text-muted">{{ $activePeriodeCount }} aktif</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Kelas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Kelas</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalKelas }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-school fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Kelompok -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Kelompok</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalKelompoks }}</div>
                                            <div class="small text-muted">{{ $activeKelompoks }} aktif</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Mahasiswa -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Mahasiswa</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalMahasiswas }}</div>
                                            <div class="small text-muted">{{ $activeMahasiswas }} aktif</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Data Pengguna & Evaluasi -->
                    <div class="row">
                        <!-- Total Users -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-secondary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Users</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users-cog fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Admin</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAdmins }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Evaluator -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Evaluator</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalEvaluators }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sesi Evaluasi -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sesi Evaluasi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSesiEvaluasi }}</div>
                                            <div class="small text-muted">{{ $activeSesiEvaluasi }} aktif</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($activePeriode)
                    <!-- Data Charts -->
                    <div class="row">
                        <!-- Kunjungan Mitra Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-building mr-2"></i>Kunjungan Mitra
                                    </h6>
                                </div>
                                <div class="card-body">
                                     <a href="/kunjungan-mitra" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-external-link-alt mr-1"></i>
                                                    Lihat Data Kunjungan Mitra
                                                </a>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Evaluasi -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-line mr-2"></i>Progress Evaluasi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-gray-800 mb-3">Proyek Cards</h6>
                                    @if($statusProyek)
                                        @foreach($statusProyek as $status => $count)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="small">{{ $status }}</span>
                                                <span class="badge badge-{{ $status === 'Selesai' ? 'success' : ($status === 'Proses' ? 'warning' : 'secondary') }}">
                                                    {{ $count }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-muted py-2">
                                            <small>Belum ada data proyek</small>
                                        </div>
                                    @endif

                                    <h6 class="font-weight-bold text-gray-800 mb-3 mt-4">Aktivitas Lists</h6>
                                    @if($statusEvaluasiAktivitas)
                                        @foreach($statusEvaluasiAktivitas as $status => $count)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="small">{{ $status }}</span>
                                                <span class="badge badge-{{ $status === 'Sudah Evaluasi' ? 'success' : 'secondary' }}">
                                                    {{ $count }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-muted py-2">
                                            <small>Belum ada data aktivitas</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Evaluasi per Kelompok -->
                    <div class="row">
                        <!-- Progress Evaluasi per Kelompok -->
                        <div class="col-xl-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Progress Evaluasi per Kelompok</h6>
                                </div>
                                <div class="card-body">
                                    @if(count($evaluasiProgress) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Kelompok</th>
                                                        <th>Progress</th>
                                                        <th>Selesai</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($evaluasiProgress as $progress)
                                                        <tr>
                                                            <td>{{ $progress['nama_kelompok'] }}</td>
                                                            <td>
                                                                <div class="progress">
                                                                    <div class="progress-bar bg-success" role="progressbar"
                                                                         style="width: {{ $progress['progress'] }}%"
                                                                         aria-valuenow="{{ $progress['progress'] }}"
                                                                         aria-valuemin="0" aria-valuemax="100">
                                                                        {{ $progress['progress'] }}%
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>{{ $progress['selesai_sesi'] }} / {{ $progress['total_sesi'] }} Sesi</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">Belum ada data evaluasi untuk periode aktif.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Aktivitas Terkini -->
                        <div class="col-xl-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Aktivitas Kunjungan Terkini</h6>
                                </div>
                                <div class="card-body">
                                    @if(count($recentKunjungan) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Kelompok</th>
                                                        <th>Mahasiswa</th>
                                                        <th>Status</th>
                                                        <th>Tanggal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentKunjungan as $kunjungan)
                                                        <tr>
                                                            <td>{{ $kunjungan->kelompok->nama_kelompok ?? '-' }}</td>
                                                            <td>{{ $kunjungan->user->name }}</td>
                                                            <td>
                                                                @php
                                                                    $statusClass = match($kunjungan->status_kunjungan) {
                                                                        'Sudah' => 'success',
                                                                        'Belum' => 'warning',
                                                                        'Tidak Ada Tanggapan' => 'danger',
                                                                        default => 'secondary'
                                                                    };
                                                                @endphp
                                                                <span class="badge bg-{{ $statusClass }}">
                                                                    {{ $kunjungan->status_kunjungan }}
                                                                </span>
                                                            </td>
                                                            <td>{{ \Carbon\Carbon::parse($kunjungan->created_at)->format('d/m/Y') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">Belum ada data kunjungan mitra.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row - Bottom Statistics -->
                    <div class="row">
                        <!-- Data Proyek -->
                        <div class="col-lg-4 mb-4">
                            <div class="card bg-info text-white shadow">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Proyek</div>
                                            <div class="h5 mb-0 font-weight-bold">{{ number_format($totalProyekLists) }} Proyek</div>
                                            <div class="text-xs mb-0">{{ number_format($totalProyekCards) }} Kartu Aktif</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-tasks fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Aktivitas -->
                        <div class="col-lg-4 mb-4">
                            <div class="card bg-secondary text-white shadow">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Aktivitas</div>
                                            <div class="h5 mb-0 font-weight-bold">{{ number_format($totalAktivitasLists) }} Aktivitas</div>
                                            <div class="text-xs mb-0">{{ $activePeriode ? number_format($aktivitasPerPeriode) . ' Periode Ini' : '-' }}</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-clipboard-list fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sesi Evaluasi -->
                        <div class="col-lg-4 mb-4">
                            <div class="card bg-dark text-white shadow">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Sesi Evaluasi</div>
                                            <div class="h5 mb-0 font-weight-bold">{{ number_format($totalSesiEvaluasi) }} Sesi</div>
                                            <div class="text-xs mb-0">{{ $activePeriode ? number_format($activeSesiEvaluasi) . ' Aktif' : '-' }}</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-clipboard-check fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center mt-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h4 class="text-gray-800">Belum Ada Periode Aktif</h4>
                        <p class="text-gray-600">Silakan buat periode baru dan atur statusnya menjadi "Aktif" untuk melihat dashboard.</p>
                        <a href="{{ route('admin.periode.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Periode Baru
                        </a>
                    </div>
                @endif

                </div>
                @endsection