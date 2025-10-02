@extends('layout.app')
@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Kelompok: {{ $kelompok->nama_kelompok }}</h1>
        <a href="{{ route('evaluator.kelompok.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Kembali
        </a>
    </div>

    <!-- Informasi Kelompok -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Kelompok</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nama Kelompok:</strong> {{ $kelompok->nama_kelompok }}</p>
                    <p><strong>Periode:</strong> {{ $kelompok->periode->periode ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Jumlah Anggota:</strong> {{ $kelompok->mahasiswas->count() }}</p>
                    <p><strong>Status Evaluasi:</strong>
                        <span class="badge {{ $kelompok->evaluation_status_badge }}">
                            {{ $kelompok->evaluation_status }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Anggota Kelompok -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Anggota Kelompok</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelompok->mahasiswas as $mahasiswa)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $mahasiswa->nim ?? '-' }}</td>
                            <td>{{ $mahasiswa->nama_mahasiswa ?? $mahasiswa->nama ?? '-' }}</td>
                            <td>
                                @if(strtolower($mahasiswa->pivot->role) === 'ketua')
                                    <span class="badge badge-primary">Ketua</span>
                                @else
                                    <span class="badge badge-secondary">Anggota</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada anggota.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Aktivitas Kelompok -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Aktivitas Kelompok</h6>
        </div>
        <div class="card-body">
            @if($kelompok->aktivitasLists->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Aktivitas</th>
                                <th>Status Evaluasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelompok->aktivitasLists as $aktivitas)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $aktivitas->nama_aktivitas ?? $aktivitas->deskripsi ?? ($aktivitas ? 'Aktivitas #' . $aktivitas->id : 'Aktivitas') }}</td>
                                <td>
                                    <span class="badge {{ $aktivitas->status_evaluasi === 'Sudah Evaluasi' ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $aktivitas->status_evaluasi }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Belum ada aktivitas untuk kelompok ini.</p>
            @endif
        </div>
    </div>

</div>
@endsection