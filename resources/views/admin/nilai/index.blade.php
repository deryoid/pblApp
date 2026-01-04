@extends('layout.app')

@section('title', 'Nilai Mahasiswa')

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
                        <div class="row">
                            <div class="col-md-3">
                                <label for="periode_id" class="form-label">Periode</label>
                                <select name="periode_id" id="periode_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Periode --</option>
                                    @foreach($periodes as $periode)
                                        <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                                            {{ $periode->periode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="kelas_id" class="form-label">Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($kelases as $kelas)
                                        <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Cari Mahasiswa</label>
                                <input type="text" name="search" id="search" class="form-control"
                                       value="{{ $search ?? '' }}" placeholder="NIM atau Nama Mahasiswa">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>


                    <!-- Tabel Nilai -->
                    @if($mahasiswaNilai->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">NIM</th>
                                        <th width="30%">Nama Mahasiswa</th>
                                        <th width="15%">Kelas</th>
                                        <th width="12%">Nilai Aktifitas</th>
                                        <th width="12%">Nilai Proyek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach($mahasiswaNilai as $data)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $data['mahasiswa']->nim }}</td>
                                            <td>{{ $data['mahasiswa']->nama_mahasiswa }}</td>
                                            <td>{{ $data['kelas']->kelas ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $data['nilai_aktifitas'] }}
                                                </span>
                                                <br>
                                                <small class="text-muted">({{ $data['total_ap'] }} AP)</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    {{ $data['nilai_proyek'] }}
                                                </span>
                                                <br>
                                                <small class="text-muted">({{ $data['total_evaluasi'] }} eval)</small>
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