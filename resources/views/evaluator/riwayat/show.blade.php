@extends('layout.app')

@section('title', 'Riwayat PBL - ' . $mahasiswa->nama_mahasiswa . ' - Dosen & Penilai PBL TRKJ Politala')

@section('content')
<div class="container-fluid px-3 px-md-4">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                <i class="fas fa-user-graduate text-primary mr-2"></i>Riwayat & Portofolio PBL: {{ $mahasiswa->nama_mahasiswa }}
            </h1>
            <p class="text-muted small mb-0">
                Laporan menyeluruh keikutsertaan PBL mahasiswa: keterangan lokasi/mitra, proyek yang dikerjakan, logbook, penilaian 4 pilar, dan kesimpulan portofolio.
            </p>
        </div>
        <div class="mt-3 mt-sm-0 d-flex gap-2">
            <a href="{{ route('evaluator.riwayat.cetak', $mahasiswa) }}" target="_blank" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
                <i class="fas fa-print mr-1"></i> Cetak Dokumen Resmi
            </a>
            <a href="{{ $backRoute ?? route('evaluator.riwayat.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @include('riwayat.dossier')

</div>
@endsection
