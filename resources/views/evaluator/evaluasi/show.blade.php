@extends('layout.app')
@section('content')
@push('styles')
<style>
  .badge-soft { background:#eef2ff; color:#4e73df; }
  .status-badge { font-weight:600; }
  .table thead th { white-space: nowrap; }
  .table td { vertical-align: middle; }
  .small-muted { font-size:.85rem; color:#6c757d; }
  .activity-box {
    display: inline-block;
    width: 12px;
    height: 12px;
    margin: 0 1px;
    border-radius: 2px;
  }
  .activity-box.evaluated {
    background-color: #28a745;
  }
  .activity-box.unevaluated {
    background-color: #343a40;
  }
</style>
@endpush

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Evaluasi Kelompok: {{ $kelompok->nama_kelompok }}</h1>
        <a href="{{ route('evaluator.evaluasi.index') }}" class="btn btn-secondary btn-sm">
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

    <!-- Progress Evaluasi -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Progress Evaluasi</h6>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="progress">
                        <?php
                        $totalActivities = $kelompok->total_activities;
                        $evaluatedActivities = $kelompok->evaluated_activities_count;
                        $percentage = $totalActivities > 0 ? ($evaluatedActivities / $totalActivities) * 100 : 0;
                        ?>
                        <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%"
                             aria-valuenow="{{ $evaluatedActivities }}" aria-valuemin="0" aria-valuemax="{{ $totalActivities }}">
                            {{ round($percentage) }}%
                        </div>
                    </div>
                    <p class="mt-2 mb-0">
                        <strong>{{ $evaluatedActivities }}</strong> dari <strong>{{ $totalActivities }}</strong> aktivitas telah dievaluasi
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="activity-boxes">
                        {!! $kelompok->activity_boxes !!}
                    </div>
                    <small class="text-muted d-block mt-2">
                        <span style="color: #28a745;">■</span> Sudah Evaluasi
                        <span style="color: #343a40;">■</span> Belum Evaluasi
                    </small>
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
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelompok->aktivitasLists as $aktivitas)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $aktivitas->name }}</td>
                                <td>
                                    <span class="badge {{ $aktivitas->status_evaluasi === 'Sudah Evaluasi' ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $aktivitas->status_evaluasi }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-circle btn-sm" title="Lihat Detail">
                                        <i class="fas fa-eye fa-sm"></i>
                                    </button>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@endsection