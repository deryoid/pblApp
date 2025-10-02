@extends('layout.app')

@section('title', 'Nilai Final Mahasiswa')

@section('content')
<div class="container-fluid">
    @push('styles')
    <style>
        .nilai-card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .nilai-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .info-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
        }
        .calculation-breakdown {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #007bff;
        }
        .ap-detail-item {
            border-left: 3px solid #17a2b8;
            background: #f8f9fa;
            transition: background-color 0.2s;
        }
        .ap-detail-item:hover {
            background: #e9ecef;
        }
        .progress-indicator {
            height: 6px;
            border-radius: 3px;
            background: #e9ecef;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
    </style>
    @endpush
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Nilai Final Mahasiswa
        </h1>
        <div>
            <a href="{{ route('evaluator.evaluasi.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('evaluator.evaluasi.nilai-final') }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="periode_id">Periode</label>
                            <select name="periode_id" id="periode_id" class="form-control">
                                <option value="">Semua Periode</option>
                                @foreach($periodes as $periode)
                                    <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                                        {{ $periode->periode }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kelompok_id">Kelompok</label>
                            <select name="kelompok_id" id="kelompok_id" class="form-control">
                                <option value="">Semua Kelompok</option>
                                @foreach($kelompoks as $kelompok)
                                    <option value="{{ $kelompok->id }}" {{ $kelompokId == $kelompok->id ? 'selected' : '' }}>
                                        {{ $kelompok->nama_kelompok }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('evaluator.evaluasi.nilai-final') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Reset
                </a>
            </form>
        </div>
    </div>

    <!-- Daftar Nilai -->
    @if($mahasiswaNilai->count() > 0)
        @foreach($mahasiswaNilai as $mahasiswaId => $data)
            <div class="nilai-card card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-graduate mr-2"></i>
                            {{ $data['mahasiswa']->nama_mahasiswa }}
                        </h6>
                        <small class="text-muted">
                            NIM: {{ $data['mahasiswa']->nim }} |
                            Kelompok: {{ $data['kelompok']->nama_kelompok }}
                        </small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-{{ $data['final_calculation']['grade'] == 'A' ? 'success' : ($data['final_calculation']['grade'] == 'B' ? 'info' : ($data['final_calculation']['grade'] == 'C' ? 'warning' : 'danger')) }} mr-2" style="font-size: 1.2rem; padding: 0.5rem 1rem;">
                            {{ $data['final_calculation']['grade'] }}
                        </span>
                        <h4 class="mb-0 ml-2">{{ round($data['final_calculation']['nilai_akhir'], 2) }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Ringkasan Nilai -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="info-badge badge-primary mb-2">Project (70%)</div>
                                <h5 class="text-primary">{{ round($data['final_calculation']['nilai_project'], 2) }}</h5>
                                <small class="text-muted">Dosen: {{ round($data['final_calculation']['avg_dosen'], 2) }} | Mitra: {{ round($data['final_calculation']['avg_mitra'], 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="info-badge badge-success mb-2">AP (30%)</div>
                                <h5 class="text-success">{{ round($data['final_calculation']['nilai_ap']['average'], 2) }}</h5>
                                <small class="text-muted">Kehadiran: {{ round($data['final_calculation']['nilai_ap']['avg_kehadiran'], 2) }} | Presentasi: {{ round($data['final_calculation']['nilai_ap']['avg_presentasi'], 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="info-badge badge-info mb-2">Total Evaluasi</div>
                                <h5 class="text-info">{{ $data['final_calculation']['total_lists'] }} Lists</h5>
                                <small class="text-muted">{{ $data['final_calculation']['total_cards'] }} Cards</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="info-badge badge-warning mb-2">Final Score</div>
                                <h5 class="text-warning">{{ round($data['final_calculation']['nilai_akhir'], 2) }}</h5>
                                <small class="text-muted">Grade: {{ $data['final_calculation']['grade'] }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold">Progress Keseluruhan</span>
                            <span class="text-muted">{{ round($data['final_calculation']['nilai_akhir']) }}%</span>
                        </div>
                        <div class="progress-indicator">
                            <div class="progress-fill" style="width: {{ round($data['final_calculation']['nilai_akhir']) }}%"></div>
                        </div>
                    </div>

                    <!-- Breakdown Per List -->
                    <div class="calculation-breakdown p-3 mb-4 rounded">
                        <h6 class="font-weight-bold mb-3">
                            <i class="fas fa-list-alt mr-2"></i>Detail Nilai per Project List
                        </h6>
                        @if($data['list_averages']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Project List</th>
                                            <th class="text-center">Dosen (80%)</th>
                                            <th class="text-center">Mitra (20%)</th>
                                            <th class="text-center">Final</th>
                                            <th class="text-center">Grade</th>
                                            <th class="text-center">Cards</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['list_averages'] as $listAvg)
                                            <tr>
                                                <td>{{ $listAvg['list']->name }}</td>
                                                <td class="text-center">{{ round($listAvg['avg_dosen'], 2) }}</td>
                                                <td class="text-center">{{ round($listAvg['avg_mitra'], 2) }}</td>
                                                <td class="text-center font-weight-bold">{{ round($listAvg['avg_card'], 2) }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-{{ $listAvg['avg_card'] >= 85 ? 'success' : ($listAvg['avg_card'] >= 75 ? 'info' : ($listAvg['avg_card'] >= 65 ? 'warning' : 'danger')) }}">
                                                        {{ $listAvg['avg_card'] >= 85 ? 'A' : ($listAvg['avg_card'] >= 75 ? 'B' : ($listAvg['avg_card'] >= 65 ? 'C' : ($listAvg['avg_card'] >= 55 ? 'D' : 'E'))) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">{{ $listAvg['count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">Belum ada data evaluasi untuk mahasiswa ini.</p>
                        @endif
                    </div>

                    <!-- Detail AP -->
                    @if($data['final_calculation']['nilai_ap']['presensi_data'] && count($data['final_calculation']['nilai_ap']['presensi_data']) > 0)
                        <div class="calculation-breakdown p-3 rounded">
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-calendar-check mr-2"></i>Detail Presensi & Presentasi
                            </h6>
                            <div class="row">
                                @foreach($data['final_calculation']['nilai_ap']['presensi_data'] as $presensi)
                                    <div class="col-md-6 mb-3">
                                        <div class="ap-detail-item p-2 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="font-weight-bold">{{ $presensi->aktivitas_list->name ?? 'Aktivitas' }}</small>
                                                <span class="badge badge-light">{{ round($presensi->nilai_final_ap, 2) }}</span>
                                            </div>
                                            <div class="row mt-1">
                                                <div class="col-6">
                                                    <small class="text-muted">Kehadiran: {{ $presensi->w_ap_kehadiran }} ({{ $presensi->kehadiran_value }})</small>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <small class="text-muted">Presentasi: {{ $presensi->w_ap_presentasi }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="card shadow mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada data nilai</h5>
                <p class="text-muted">Silakan pilih periode dan kelompok yang memiliki data evaluasi.</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when select changes
    $('#periode_id, #kelompok_id').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
@endsection