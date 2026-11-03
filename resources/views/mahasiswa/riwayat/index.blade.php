@extends('layout.app')

@section('title', 'Riwayat PBL Saya - PBL TRKJ Politala')

@section('content')
<div class="container-fluid px-3 px-md-4">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                <i class="fas fa-history text-primary mr-2"></i>Riwayat & Portofolio PBL Saya
            </h1>
            <p class="text-muted small mb-0">
                Rekam jejak komprehensif keikutsertaan PBL, mitra/lokasi pengerjaan, capaian 4 pilar, logbook, dan kesimpulan portofolio SKPI.
            </p>
        </div>
        <div class="mt-3 mt-sm-0 d-flex gap-2">
            <a href="{{ route('mahasiswa.riwayat.cetak') }}" target="_blank" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
                <i class="fas fa-print mr-1"></i> Cetak / Simpan PDF
            </a>
            <a href="{{ url('/mahasiswa') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Beranda
            </a>
        </div>
    </div>

    @if($mahasiswa)
        @include('riwayat.dossier')
    @else
        <div class="alert alert-warning">
            Data profil mahasiswa Anda belum lengkap. Silakan lengkapi profil terlebih dahulu.
        </div>
    @endif

</div>
@endsection
