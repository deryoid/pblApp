@extends('layout.app')

@section('title', 'Nilai Mahasiswa - PBL TRKJ Politala')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Nilai Mahasiswa
        </h1>
        <div>
            <a href="{{ url('/admin') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Nilai Mahasiswa</h6>
                    <a href="{{ route('admin.nilai.export', [
                        'periode_id' => $periodeId,
                        'kelas_id' => $kelasId,
                        'search' => $search
                    ]) }}" class="btn btn-success btn-sm" id="exportBtn">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="mb-4" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label for="periode_id" class="form-label font-weight-bold">Periode</label>
                                <select name="periode_id" id="periode_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- Semua Periode --</option>
                                    @foreach($periodes as $periode)
                                        <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                                            {{ $periode->periode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label for="kelas_id" class="form-label font-weight-bold">Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- Seluruh Mahasiswa --</option>
                                    @foreach($kelases as $kelas)
                                        <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="search" class="form-label font-weight-bold">Cari Mahasiswa</label>
                                <div class="input-group">
                                    <input type="text" name="search" id="search" class="form-control"
                                           value="{{ $search ?? '' }}" placeholder="NIM atau Nama Mahasiswa">
                                </div>
                            </div>
                            <div class="col-md-2 mb-3 mb-md-0">
                                <button type="submit" class="btn btn-primary" title="Cari">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetFilters()" title="Reset Filter">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                        </div>
                    </form>


                    <!-- Tabel Nilai -->
                    @if($mahasiswaNilai->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover small" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="text-center no-sort" style="width: 45px;">No</th>
                                        <th style="width: 130px;">NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th style="width: 120px;">Kelas</th>
                                        <th class="text-center" style="width: 130px;">Nilai Aktivitas</th>
                                        <th class="text-center" style="width: 130px;">Nilai Proyek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mahasiswaNilai as $data)
                                        <tr>
                                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                            <td class="align-middle font-weight-bold text-nowrap text-primary">{{ $data['mahasiswa']->nim }}</td>
                                            <td class="align-middle font-weight-500">{{ $data['mahasiswa']->nama_mahasiswa }}</td>
                                            <td class="align-middle text-nowrap">
                                                <span class="badge badge-light border text-dark">{{ $data['kelas']->kelas ?? '-' }}</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-info px-2.5 py-1 font-weight-bold" style="font-size: 0.85rem;">
                                                    {{ $data['nilai_aktifitas'] }}
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-primary px-2.5 py-1 font-weight-bold" style="font-size: 0.85rem;">
                                                    {{ $data['nilai_proyek'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada data nilai yang tersedia untuk filter yang dipilih.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetFilters() {
    window.location.href = '{{ route("admin.nilai.index") }}';
}

// Auto-submit search on Enter key
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});

// Update export button URL when filters change
function updateExportButton() {
    const periodeId = document.getElementById('periode_id').value;
    const kelasId = document.getElementById('kelas_id').value;
    const search = document.getElementById('search').value;

    let exportUrl = '{{ route('admin.nilai.export') }}';
    const params = new URLSearchParams();

    if (periodeId) params.append('periode_id', periodeId);
    if (kelasId) params.append('kelas_id', kelasId);
    if (search) params.append('search', search);

    if (params.toString()) {
        exportUrl += '?' + params.toString();
    }

    document.getElementById('exportBtn').href = exportUrl;
}

// Listen for filter changes
document.getElementById('periode_id').addEventListener('change', updateExportButton);
document.getElementById('kelas_id').addEventListener('change', updateExportButton);
document.getElementById('search').addEventListener('input', updateExportButton);
</script>
@endpush