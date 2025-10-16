@extends('layout.app')

@section('title', 'Nilai Saya')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Nilai Saya
        </h1>
        <div>
            <a href="{{ url('/mahasiswa') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nilai Mahasiswa di Kelompok Saya</h6>
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
                                <label for="kelas_id" class="form-label">Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($kelasOptions as $kelas)
                                        <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Cari Mahasiswa</label>
                                <div class="input-group">
                                    <input type="text" name="search" id="search" class="form-control"
                                           value="{{ $search ?? '' }}" placeholder="NIM atau Nama Mahasiswa">
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
                                        <th width="12%">NIM</th>
                                        <th width="33%">Nama Mahasiswa</th>
                                        <th width="15%">Kelas</th>
                                        <th width="12%">Nilai Aktifitas</th>
                                        <th width="12%">Nilai Proyek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach($mahasiswaNilai as $data)
                                        <tr class="{{ $data['mahasiswa']->user_id == Auth::id() ? 'table-primary font-weight-bold' : '' }}">
                                            <td>
                                                {{ $no++ }}
                                                @if($data['mahasiswa']->user_id == Auth::id())
                                                    <i class="fas fa-user text-primary" title="Anda"></i>
                                                @endif
                                            </td>
                                            <td>{{ $data['mahasiswa']->nim }}</td>
                                            <td>
                                                {{ $data['mahasiswa']->nama_mahasiswa }}
                                                @if($data['mahasiswa']->user_id == Auth::id())
                                                    <span class="badge badge-primary ml-2">Anda</span>
                                                @endif
                                            </td>
                                            <td>{{ $data['kelas']->kelas ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $data['nilai_aktifitas'] >= 80 ? 'success' : ($data['nilai_aktifitas'] >= 70 ? 'info' : ($data['nilai_aktifitas'] >= 60 ? 'warning' : 'danger')) }}">
                                                    {{ $data['nilai_aktifitas'] }}
                                                </span>
                                                <br>
                                                <small class="text-muted">({{ $data['total_ap'] }} AP)</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $data['nilai_proyek'] >= 80 ? 'success' : ($data['nilai_proyek'] >= 70 ? 'info' : ($data['nilai_proyek'] >= 60 ? 'warning' : 'danger')) }}">
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

       
                        <!-- My Score Highlight -->
                        @php
                            $myScore = $mahasiswaNilai->firstWhere('mahasiswa.user_id', Auth::id());
                        @endphp
                        @if($myScore)
                            <div class="alert alert-info mt-4">
                                <h5 class="alert-heading">
                                    <i class="fas fa-user-graduate"></i> Nilai Saya
                                </h5>
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>Nilai Aktifitas:</strong><br>
                                        <span class="badge badge-info badge-lg">{{ $myScore['nilai_aktifitas'] }}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Nilai Proyek:</strong><br>
                                        <span class="badge badge-primary badge-lg">{{ $myScore['nilai_proyek'] }}</span>
                                    </div>
                                       <div class="col-md-8">
                                        <small class="text-muted">
                                            Peringkat Anda: {{ $mahasiswaNilai->sortByDesc('nilai_akhir')->search(function($item) use ($myScore) {
                                                return $item['mahasiswa']->id === $myScore['mahasiswa']->id;
                                            }) + 1 }} dari {{ $mahasiswaNilai->count() }} mahasiswa
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Belum ada data nilai yang tersedia untuk filter yang dipilih.
                            Pastikan Anda sudah tergabung dalam kelompok dan ada penilaian yang telah dilakukan.
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
    window.location.href = '{{ route("mahasiswa.nilai.index") }}';
}

// Auto-submit search on Enter key
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});

// Highlight current user's row on page load
document.addEventListener('DOMContentLoaded', function() {
    const myRow = document.querySelector('tr.table-primary');
    if (myRow) {
        myRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
@endpush

@push('styles')
<style>
.badge-lg {
    font-size: 1.2rem;
    padding: 0.5rem 1rem;
}
</style>
@endpush