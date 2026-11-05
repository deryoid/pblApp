@extends('layout.app')
@section('head')
<style>
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: " ";
    position: absolute;
    top: 0;
    left: 20px;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
}

.timeline-marker {
    position: absolute;
    left: 10px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid #4e73df;
}

.timeline-title {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
    color: #5a5c69;
}

.timeline-text {
    margin: 0;
    font-size: 12px;
    color: #858796;
}
</style>
@endsection
@section('content')
                <div class="container-fluid">
                    <!-- Page Heading with Periode Switcher -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Dashboard Evaluator</h1>
                            <p class="text-secondary small mb-0 font-weight-500">
                                Ringkasan evaluasi, penilaian, dan aktivitas proyek mahasiswa per periode.
                            </p>
                        </div>
                        <div class="mt-3 mt-sm-0 d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                            <form method="GET" action="{{ url('evaluator') }}" class="m-0 d-flex align-items-center">
                                <label for="periodeSwitchEvaluator" class="small font-weight-bold text-gray-700 mr-2 mb-0 text-nowrap">
                                    <i class="fas fa-calendar-alt text-primary mr-1" aria-hidden="true"></i>Pilih Periode:
                                </label>
                                <select name="periode_id" id="periodeSwitchEvaluator" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="min-width: 200px;">
                                    @foreach($periodes as $p)
                                        <option value="{{ $p->id }}" {{ $selectedPeriode && $selectedPeriode->id == $p->id ? 'selected' : '' }}>
                                            {{ $p->periode }} {{ $p->status_periode === 'Aktif' ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fas fa-external-link-alt mr-1" aria-hidden="true"></i> Showcase SPA
                            </a>
                            <a href="{{ route('evaluator.evaluasi.index') }}" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-clipboard-check mr-1" aria-hidden="true"></i> Kelola Evaluasi
                            </a>
                        </div>
                    </div>

                    @if($selectedPeriode)
                    <!-- Info Periode Terpilih -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm border border-gray-200 py-2">
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 mb-2 mb-md-0 border-md-right">
                                            <div class="d-flex align-items-center">
                                                <div class="p-2.5 rounded-circle bg-primary-soft text-primary mr-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                                    <i class="fas fa-calendar-alt fa-lg" aria-hidden="true"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                                                        <h2 class="h5 mb-0 font-weight-bold text-gray-900">{{ $selectedPeriode->periode }}</h2>
                                                        @if($selectedPeriode->status_periode === 'Aktif')
                                                            <span class="badge badge-success px-2 py-0.5">Aktif</span>
                                                        @else
                                                            <span class="badge badge-secondary px-2 py-0.5">{{ $selectedPeriode->status_periode ?: 'Selesai' }}</span>
                                                        @endif
                                                    </div>
                                                    @if($selectedPeriode->tanggal_mulai && $selectedPeriode->tanggal_selesai)
                                                        <div class="small text-secondary mt-0.5">
                                                            <i class="far fa-clock mr-1" aria-hidden="true"></i>
                                                            {{ \Carbon\Carbon::parse($selectedPeriode->tanggal_mulai)->format('d M Y') }} -
                                                            {{ \Carbon\Carbon::parse($selectedPeriode->tanggal_selesai)->format('d M Y') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="d-flex align-items-center justify-content-around flex-wrap text-center py-1" style="gap: 1rem;">
                                                <div>
                                                    <div class="h5 mb-0 font-weight-bold text-primary">{{ $activeKelompoks }}</div>
                                                    <span class="small text-muted font-weight-500">Kelompok Periode Ini</span>
                                                </div>
                                                <div class="border-left pl-3">
                                                    <div class="h5 mb-0 font-weight-bold text-success">{{ $activeMahasiswas }}</div>
                                                    <span class="small text-muted font-weight-500">Mahasiswa</span>
                                                </div>
                                                <div class="border-left pl-3">
                                                    <div class="h5 mb-0 font-weight-bold text-info">{{ $proyekToEvaluate }}</div>
                                                    <span class="small text-muted font-weight-500">Proyek</span>
                                                </div>
                                                <div class="border-left pl-3">
                                                    <div class="h5 mb-0 font-weight-bold text-warning">{{ $activeSesiEvaluasi }}</div>
                                                    <span class="small text-muted font-weight-500">Sesi Evaluasi</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Akademik & Mahasiswa -->
                    <div class="row">
                        <!-- Total Kelompok -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Kelompok</div>
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
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Mahasiswa</div>
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

                        <!-- Evaluasi Pending -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Evaluasi Pending</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingEvaluations }}</div>
                                            <div class="small text-muted">{{ $completedEvaluations }} selesai</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Sesi Evaluasi -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Sesi Evaluasi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSesiEvaluasi }}</div>
                                            <div class="small text-muted">{{ $activeSesiEvaluasi }} periode ini</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Evaluasi & Tugas Hari Ini -->
                    <div class="row">
                        <!-- Progress Evaluasi per Kelompok -->
                        <div class="col-xl-8">
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
                                                        <th>Sesi Selesai</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($evaluasiProgress as $progress)
                                                        <tr>
                                                            <td>{{ $progress['nama_kelompok'] }}</td>
                                                            <td>
                                                                <div class="progress">
                                                                    <div class="progress-bar bg-{{ $progress['progress'] == 100 ? 'success' : ($progress['progress'] >= 50 ? 'warning' : 'danger') }}" role="progressbar"
                                                                         style="width: {{ $progress['progress'] }}%"
                                                                         aria-valuenow="{{ $progress['progress'] }}"
                                                                         aria-valuemin="0" aria-valuemax="100">
                                                                        {{ $progress['progress'] }}%
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>{{ $progress['selesai_sesi'] }} / {{ $progress['total_sesi'] }}</td>
                                                            <td>
                                                                @if($progress['progress'] == 100)
                                                                    <span class="badge bg-success">Selesai</span>
                                                                @else
                                                                    <span class="badge bg-warning">Proses</span>
                                                                @endif
                                                            </td>
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

                        <!-- Tugas Evaluasi Hari Ini -->
                        <div class="col-xl-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Jadwal Evaluasi Hari Ini</h6>
                                </div>
                                <div class="card-body">
                                    @if(count($todayEvaluations) > 0)
                                        <div class="timeline">
                                            @foreach($todayEvaluations as $evaluation)
                                                <div class="timeline-item mb-3">
                                                    <div class="timeline-marker bg-{{ $evaluation->status == 'Selesai' ? 'success' : 'primary' }}"></div>
                                                    <div class="timeline-content">
                                                        <h6 class="timeline-title">{{ $evaluation->kelompok->nama_kelompok ?? '-' }}</h6>
                                                        <p class="timeline-text small text-muted mb-1">
                                                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($evaluation->tanggal)->format('H:i') }}
                                                        </p>
                                                        <span class="badge bg-{{ $evaluation->status == 'Selesai' ? 'success' : 'warning' }}">
                                                            {{ $evaluation->status }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                            <p>Tidak ada jadwal evaluasi hari ini</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Tugas Evaluasi -->
                    <div class="row">
                        <!-- Proyek yang Perlu Dievaluasi -->
                        <div class="col-xl-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-tasks mr-2"></i>Proyek yang Perlu Dievaluasi
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="h3 text-warning mb-2">{{ $proyekToEvaluate }}</div>
                                    <p class="text-muted mb-0">dari {{ $totalProyekCards }} total proyek cards</p>
                                    <a href="{{ route('evaluator.evaluasi.index') }}" class="btn btn-primary btn-sm mt-3">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Aktivitas yang Perlu Dievaluasi -->
                        <div class="col-xl-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-clipboard-list mr-2"></i>Aktivitas yang Perlu Dievaluasi
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="h3 text-info mb-2">{{ $aktivitasToEvaluate }}</div>
                                    <p class="text-muted mb-0">dari {{ $totalAktivitasLists }} total aktivitas lists</p>
                                    <a href="{{ route('evaluator.evaluasi.index') }}" class="btn btn-primary btn-sm mt-3">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center mt-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h4 class="text-gray-800">Belum Ada Periode Aktif</h4>
                        <p class="text-gray-600">Dashboard evaluator hanya tersedia saat ada periode aktif.</p>
                    </div>
                    @endif

                </div>
@endsection