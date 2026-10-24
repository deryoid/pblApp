@extends('layout.app')

@section('title', 'Nilai Saya - SIMEP Politala')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                <i class="fas fa-chart-line text-primary mr-2"></i>Transkrip Nilai Akademik PBL
            </h1>
            <p class="text-muted small mb-0">
                Ringkasan penilaian aktivitas kelompok, penilaian proyek mandiri/mitra, dan evaluasi hasil belajar
            </p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ url('/mahasiswa') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <!-- Tabel Nilai -->
    @if($mahasiswaNilai->count() > 0)
        <div class="row">
            @foreach($mahasiswaNilai as $data)
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 rounded-xl overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-dark">
                                <i class="fas fa-user-graduate text-primary mr-2"></i>Lembar Nilai: {{ $data['mahasiswa']->nama_mahasiswa }}
                            </h6>
                            <span class="badge badge-primary px-3 py-1">
                                Kelas: {{ $data['kelas']->kelas ?? '-' }}
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-5 mb-4 mb-lg-0 border-right-lg">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-circle bg-primary text-white font-weight-bold d-flex align-items-center justify-content-center mr-3" style="width:52px;height:52px;font-size:1.25rem;">
                                            {{ strtoupper(substr($data['mahasiswa']->nama_mahasiswa ?? 'M', 0, 2)) }}
                                        </div>
                                        <div>
                                            <h5 class="font-weight-bold text-dark mb-0">{{ $data['mahasiswa']->nama_mahasiswa }}</h5>
                                            <div class="text-muted small">NIM: <strong>{{ $data['mahasiswa']->nim }}</strong></div>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Nilai berikut dihitung berdasarkan akumulasi absensi logbook aktivitas harian dan rubrik penilaian proyek oleh dosen serta mitra industri.
                                    </p>
                                </div>

                                <div class="col-lg-7">
                                    <div class="row g-3 text-center">
                                        <!-- Nilai Aktivitas -->
                                        <div class="col-6 col-sm-6 mb-3">
                                            <div class="p-3 bg-light rounded-xl border">
                                                <div class="text-xs font-weight-bold text-uppercase text-muted mb-1">Nilai Aktivitas</div>
                                                <h3 class="font-weight-bold text-primary mb-0">{{ $data['nilai_aktifitas'] }}</h3>
                                                <small class="text-muted">Bobot Harian & AP</small>
                                            </div>
                                        </div>

                                        <!-- Nilai Proyek -->
                                        <div class="col-6 col-sm-6 mb-3">
                                            <div class="p-3 bg-light rounded-xl border">
                                                <div class="text-xs font-weight-bold text-uppercase text-muted mb-1">Nilai Proyek</div>
                                                <h3 class="font-weight-bold text-success mb-0">{{ $data['nilai_proyek'] }}</h3>
                                                <small class="text-muted">Rubrik Luaran Proyek</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm border-0 rounded-xl p-5 text-center">
            <div class="mb-3">
                <i class="fas fa-clipboard-check fa-3x text-muted" style="opacity: 0.3;"></i>
            </div>
            <h5 class="font-weight-bold text-dark mb-1">Belum Ada Transkrip Nilai Tersedia</h5>
            <p class="text-muted small mb-0">Penilaian evaluasi belum dirilis atau sedang dalam tahap rekapitulasi oleh tim evaluator dosen.</p>
        </div>
    @endif
</div>
@endsection
