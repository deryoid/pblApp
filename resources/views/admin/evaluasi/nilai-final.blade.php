@extends('layout.app')

@section('title', 'Nilai Final Mahasiswa')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Nilai Final Mahasiswa
        </h1>
        <div>
            <a href="{{ route('admin.evaluasi.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Nilai Final Per Mahasiswa</h6>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label for="kelompok_id" class="form-label">Kelompok</label>
                                <select name="kelompok_id" id="kelompok_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Kelompok --</option>
                                    @foreach($kelompoks as $kelompok)
                                        <option value="{{ $kelompok->id }}" {{ $kelompokId == $kelompok->id ? 'selected' : '' }}>
                                            {{ $kelompok->nama_kelompok }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tabel Nilai Final -->
                    @if($mahasiswaNilai->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Kelompok</th>
                                        <th>Project</th>
                                        <th>Nilai Akhir</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                        <th>Tanggal Evaluasi</th>
                                        <th>Evaluator</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach($mahasiswaNilai as $mahasiswaId => $data)
                                        @foreach($data['evaluations'] as $index => $evaluation)
                                            <tr>
                                                @if($index == 0)
                                                    <td rowspan="{{ count($data['evaluations']) }}">{{ $no++ }}</td>
                                                    <td rowspan="{{ count($data['evaluations']) }}">{{ $data['mahasiswa']->nim }}</td>
                                                    <td rowspan="{{ count($data['evaluations']) }}">{{ $data['mahasiswa']->nama_mahasiswa }}</td>
                                                    <td rowspan="{{ count($data['evaluations']) }}">{{ $data['kelompok']->nama_kelompok }}</td>
                                                @endif
                                                <td>{{ $evaluation['project']->title }}</td>
                                                <td>
                                                    @if($evaluation['nilai_akhir'])
                                                        <span class="badge bg-primary">{{ number_format($evaluation['nilai_akhir'], 2) }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($evaluation['grade'])
                                                        <span class="badge @if($evaluation['grade'] == 'A') bg-success @elseif($evaluation['grade'] == 'B') bg-info @elseif($evaluation['grade'] == 'C') bg-warning @elseif($evaluation['grade'] == 'D') bg-danger @else bg-secondary @endif">
                                                            {{ $evaluation['grade'] }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge @if($evaluation['status'] == 'submitted') bg-success @elseif($evaluation['status'] == 'locked') bg-danger @else bg-secondary @endif">
                                                        {{ ucfirst($evaluation['status']) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($evaluation['tanggal_evaluasi'])
                                                        {{ date('d/m/Y', strtotime($evaluation['tanggal_evaluasi'])) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($evaluation['evaluator'])
                                                        {{ $evaluation['evaluator']->name }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Ringkasan Statistik -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Mahasiswa</span>
                                        <span class="info-box-number">{{ $mahasiswaNilai->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sudah Dinilai</span>
                                        <span class="info-box-number">{{ $mahasiswaNilai->where('evaluations.0.status', 'submitted')->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Draft</span>
                                        <span class="info-box-number">{{ $mahasiswaNilai->where('evaluations.0.status', 'draft')->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-primary">
                                    <span class="info-box-icon"><i class="fas fa-trophy"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Rata-rata Nilai</span>
                                        <span class="info-box-number">
                                            {{ $mahasiswaNilai->flatMap->evaluations->whereNotNull('nilai_akhir')->avg('nilai_akhir') ? number_format($mahasiswaNilai->flatMap->evaluations->whereNotNull('nilai_akhir')->avg('nilai_akhir'), 2) : '0' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
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

@push('styles')
<style>
    .info-box {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .info-box-icon {
        border-radius: 0.5rem 0 0 0.5rem;
    }
    .table th {
        background-color: #343a40;
        color: white;
        font-weight: 600;
    }
    .badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
function resetFilters() {
    window.location.href = '{{ route("admin.evaluasi.nilai-final") }}';
}

// Auto-refresh setiap 30 detik jika ada perubahan data
setInterval(function() {
    if (document.hasFocus()) {
        // Hanya refresh jika user sedang melihat halaman ini
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);

        fetch('{{ route("admin.evaluasi.nilai-final") }}?' + params.toString())
            .then(response => response.text())
            .then(html => {
                // Update hanya jika ada perubahan (sederhana)
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const newTable = newDoc.querySelector('table tbody');
                const currentTable = document.querySelector('table tbody');

                if (newTable && currentTable && newTable.innerHTML !== currentTable.innerHTML) {
                    location.reload();
                }
            })
            .catch(error => console.log('Auto-refresh error:', error));
    }
}, 30000);
</script>
@endpush