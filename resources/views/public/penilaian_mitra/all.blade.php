@extends('layout.public')

@section('title', 'Rekap Penilaian Mitra - SIMEP Politala')

@section('content')
<div class="container-fluid px-0">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                <i class="fas fa-star text-warning mr-2" aria-hidden="true"></i>
                Data Penilaian Mitra Seluruh Proyek
            </h1>
            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                Rekapitulasi evaluasi performa, keaktifan, dan penilaian kualitas luaran proyek oleh mitra industri
            </p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-th-large mr-1"></i> Showcase Proyek SPA
            </a>
        </div>
    </div>

    <!-- Penilaian Mitra Card -->
    <div class="card shadow-sm border-0 rounded-xl overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-award text-primary mr-2"></i>Daftar Evaluasi Nilai Mitra
            </h6>
        </div>
        <div class="card-body p-4">
            @if($evaluations->count() > 0)
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%" class="text-center" style="white-space: nowrap;">No</th>
                                <th style="min-width: 220px;">Proyek & Mitra</th>
                                <th style="white-space: nowrap;">Kelompok</th>
                                <th style="min-width: 180px;">Mahasiswa Dinilai</th>
                                <th class="text-center" style="white-space: nowrap;">Komunikasi</th>
                                <th class="text-center" style="white-space: nowrap;">Kualitas Hasil</th>
                                <th class="text-center" style="white-space: nowrap;">Nilai Akhir</th>
                                <th class="text-center" style="white-space: nowrap;">Grade</th>
                                <th style="white-space: nowrap;">Tanggal Evaluasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = $evaluations->firstItem() ?? 1; @endphp
                            @foreach($evaluations as $eval)
                                <tr>
                                    <td class="text-center text-muted font-weight-bold" style="white-space: nowrap;">{{ $no++ }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark mb-1 text-wrap-neat" style="line-height: 1.35;">
                                            {{ $eval->projectCard->title ?? 'Proyek PBL' }}
                                        </div>
                                        @if($eval->projectCard && $eval->projectCard->nama_mitra)
                                            <span class="badge badge-primary">
                                                <i class="fas fa-building mr-1"></i> {{ $eval->projectCard->nama_mitra }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">Proyek Internal</span>
                                        @endif
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <span class="font-weight-600 text-dark">
                                            {{ $eval->kelompok->nama_kelompok ?? 'Kelompok' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white font-weight-bold d-flex align-items-center justify-content-center mr-2 flex-shrink-0" style="width:34px;height:34px;font-size:0.8rem;">
                                                {{ strtoupper(substr($eval->mahasiswa->nama_mahasiswa ?? 'M', 0, 2)) }}
                                            </div>
                                            <div class="text-truncate" style="max-width: 160px;">
                                                <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.875rem;">
                                                    {{ $eval->mahasiswa->nama_mahasiswa ?? 'Mahasiswa' }}
                                                </div>
                                                <small class="text-muted d-block">NIM: {{ $eval->mahasiswa->nim ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <span class="badge badge-info">
                                            {{ $eval->m_kehadiran ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <span class="badge badge-info">
                                            {{ $eval->m_presentasi ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <span class="font-weight-bold text-dark" style="font-size: 1rem;">
                                            {{ number_format($eval->nilai_akhir ?? 0, 1) }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        @php
                                            $gradeClass = match($eval->grade) {
                                                'A' => 'badge-success',
                                                'B' => 'badge-primary',
                                                'C' => 'badge-warning',
                                                default => 'badge-danger',
                                            };
                                        @endphp
                                        <span class="badge {{ $gradeClass }} px-3 py-1 font-weight-bold">
                                            {{ $eval->grade ?? 'E' }}
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <small class="text-muted font-weight-500">
                                            {{ $eval->created_at ? $eval->created_at->format('d M Y') : '-' }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 pt-3 border-top">
                    <small class="text-muted mb-2 mb-sm-0">
                        Menampilkan {{ $evaluations->firstItem() }} sampai {{ $evaluations->lastItem() }} dari {{ $evaluations->total() }} entri evaluasi
                    </small>
                    {{ $evaluations->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-clipboard-list fa-3x text-muted" style="opacity: 0.4;"></i>
                    </div>
                    <h5 class="text-dark font-weight-bold mb-1">Belum Ada Data Penilaian Mitra</h5>
                    <p class="text-muted small mb-4">Penilaian mitra akan ditampilkan setelah mitra industri mengisi formulir evaluasi.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Showcase Proyek
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
