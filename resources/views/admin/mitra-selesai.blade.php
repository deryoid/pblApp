@extends('layout.app')
@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mitra Proyek Selesai</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.mitra-selesai') }}" class="btn btn-sm btn-secondary mr-2">
                <i class="fas fa-times mr-1"></i> Reset Filter
            </a>
            <a href="{{ route('admin.mitra-selesai', request()->query()) }}" class="btn btn-sm btn-primary mr-2">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </a>
            <a href="{{ route('admin.mitra-selesai.export', request()->query()) }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
        </div>
    </div>

    @if($mitraGrouped->count() > 0)
        <!-- Filter Section -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter mr-2"></i>Filter
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.mitra-selesai') }}">
                    <div class="row">
                        <div class="col-md-5">
                            <label for="kelompok_id" class="form-label">Kelompok</label>
                            <select name="kelompok_id" id="kelompok_id" class="form-control form-select">
                                <option value="">Semua Kelompok</option>
                                @foreach($allKelompok as $kelompok)
                                    <option value="{{ $kelompok->id }}" {{ $kelompokId == $kelompok->id ? 'selected' : '' }}>
                                        {{ $kelompok->nama_kelompok }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="periode_id" class="form-label">
                                Periode
                                @if($activePeriode)
                                    <span class="badge badge-success ml-1">Aktif: {{ $activePeriode->periode }}</span>
                                @endif
                            </label>
                            <select name="periode_id" id="periode_id" class="form-control form-select">
                                <option value="">Semua Periode</option>
                                @if($activePeriode && $allPeriode->where('status_periode', 'Aktif')->isNotEmpty())
                                    <optgroup label="🟢 Periode Aktif">
                                        @foreach($allPeriode->where('status_periode', 'Aktif') as $periode)
                                            <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                                                🟢 {{ $periode->periode }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="⚪ Periode Lainnya">
                                        @foreach($allPeriode->where('status_periode', '!=', 'Aktif') as $periode)
                                            <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                                                ⚪ {{ $periode->periode }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @else
                                    @foreach($allPeriode as $periode)
                                        <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                                            {{ $periode->periode }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Mitra</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $mitraGrouped->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Proyek Selesai</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $mitraSelesai->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Kelompok</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $rekapPerKelompok->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Periode</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $rekapKeseluruhan->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rekap Keseluruhan -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar mr-2"></i>Rekap Keseluruhan
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th class="text-center">Jumlah Mitra</th>
                                <th class="text-center">Jumlah Proyek</th>
                                <th class="text-center">Rata-rata Proyek/Mitra</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekapKeseluruhan as $rekap)
                            <tr>
                                <td><strong>{{ $rekap['nama_periode'] }}</strong></td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $rekap['jumlah_mitra'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ $rekap['jumlah_proyek'] }}</span>
                                </td>
                                <td class="text-center">
                                    {{ number_format($rekap['jumlah_proyek'] / max($rekap['jumlah_mitra'], 1), 1) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold bg-light">
                                <td>Total</td>
                                <td class="text-center">{{ $mitraGrouped->count() }}</td>
                                <td class="text-center">{{ $mitraSelesai->count() }}</td>
                                <td class="text-center">
                                    {{ number_format($mitraSelesai->count() / max($mitraGrouped->count(), 1), 1) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rekap Per Kelompok -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-users mr-2"></i>Rekap Per Kelompok
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="kelompokTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Kelompok</th>
                                <th class="text-center">Jumlah Mitra</th>
                                <th class="text-center">Jumlah Proyek</th>
                                <th>Daftar Mitra</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekapPerKelompok as $index => $rekap)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $rekap['nama_kelompok'] }}</strong>
                                    @if($kelompokId && $rekap['kelompok_id'] == $kelompokId)
                                        <i class="fas fa-check-circle text-success ml-2"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $rekap['jumlah_mitra'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ $rekap['jumlah_proyek'] }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($rekap['mitra'] as $mitra)
                                            <span class="badge badge-info">{{ $mitra }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Daftar Mitra -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-building mr-2"></i>Daftar Mitra
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="mitraTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Mitra</th>
                                <th>Kontak</th>
                                <th class="text-center">Jumlah Proyek</th>
                                <th>Proyek</th>
                                <th width="120">Terakhir Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mitraGrouped as $index => $mitra)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $mitra['nama_mitra'] }}</strong>
                                </td>
                                <td>{{ $mitra['kontak_mitra'] ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $mitra['jumlah_proyek'] }}</span>
                                </td>
                                <td>
                                    <div class="small">
                                        @foreach($mitra['proyek'] as $proyek)
                                            <div class="mb-1">
                                                <i class="fas fa-tasks text-info mr-1"></i>
                                                {{ $proyek['title'] }}
                                                <span class="text-muted">
                                                    ({{ $proyek['kelompok'] }}, {{ $proyek['periode'] }})
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($mitra['proyek'][0]['updated_at'])->format('d M Y') }}
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list mr-2"></i>Detail Semua Proyek Selesai
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered small" id="detailTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Mitra</th>
                                <th>Judul Proyek</th>
                                <th>Kelompok</th>
                                <th>Periode</th>
                                <th>Skema PBL</th>
                                <th>Progress</th>
                                <th>Terakhir Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mitraSelesai as $index => $proyek)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $proyek['nama_mitra'] }}</strong></td>
                                <td>{{ $proyek['title'] }}</td>
                                <td>{{ $proyek['kelompok'] }}</td>
                                <td>{{ $proyek['periode'] }}</td>
                                <td>{{ $proyek['skema_pbl'] }}</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $proyek['progress'] }}%"
                                            aria-valuenow="{{ $proyek['progress'] }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                            {{ $proyek['progress'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($proyek['updated_at'])->format('d M Y H:i') }}
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="fas fa-building fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-800">Belum Ada Data Mitra</h5>
                    <p class="text-gray-600">Belum ada proyek dengan status "Selesai" yang memiliki data mitra untuk filter yang dipilih.</p>
                    <a href="{{ route('admin.mitra-selesai') }}" class="btn btn-primary">
                        <i class="fas fa-times mr-1"></i> Hapus Filter
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    $(document).ready(function() {
        $('#kelompokTable').DataTable({
            pageLength: 25,
            order: [[2, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });

        $('#mitraTable').DataTable({
            pageLength: 25,
            order: [[3, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });

        $('#detailTable').DataTable({
            pageLength: 25,
            order: [[7, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });
    });
</script>
@endsection
