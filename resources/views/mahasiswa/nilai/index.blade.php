@extends('layout.app')

@section('title', 'Nilai Saya')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Nilai Mahasiswa
        </h1>
        <div>
            <a href="{{ url('/mahasiswa') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Tabel Nilai -->
    @if($mahasiswaNilai->count() > 0)
        <div class="row">
            @foreach($mahasiswaNilai as $data)
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Nilai Saya</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="font-weight-bold">{{ $data['mahasiswa']->nama_mahasiswa }}</h5>
                                    <p class="text-muted mb-1">NIM: {{ $data['mahasiswa']->nim }}</p>
                                    <p class="text-muted mb-0">Kelas: {{ $data['kelas']->kelas ?? '-' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <h4 class="text-primary font-weight-bold">{{ $data['nilai_aktifitas'] }}</h4>
                                                <small class="text-muted">Nilai Aktifitas</small>
                                                <br>
                                                <span class="badge badge-info">{{ $data['total_ap'] }} AP</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <h4 class="text-success font-weight-bold">{{ $data['nilai_proyek'] }}</h4>
                                                <small class="text-muted">Nilai Proyek</small>
                                                <br>
                                                <span class="badge badge-primary">{{ $data['total_evaluasi'] }} eval</span>
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
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Belum ada data nilai yang tersedia.
        </div>
    @endif
</div>
@endsection