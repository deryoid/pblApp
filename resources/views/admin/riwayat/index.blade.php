@extends('layout.app')

@section('title', 'Riwayat PBL Mahasiswa - Administrator PBL TRKJ Politala')

@section('content')
<div class="container-fluid px-3 px-md-4">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                <i class="fas fa-history text-primary mr-2"></i>Riwayat PBL Per Orang (Mahasiswa)
            </h1>
            <p class="text-muted small mb-0">
                Pusat data portofolio riwayat pengerjaan proyek mahasiswa: lokasi/mitra PBL, tugas teknis yang dikerjakan, evaluasi 4 pilar, dan kesimpulan portofolio SKPI.
            </p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user-graduate mr-1"></i> Bank Data Mahasiswa
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border border-gray-200 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.riwayat.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="riwayatSearch" class="small font-weight-bold text-gray-700">Pencarian NIM / Nama</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search" aria-hidden="true"></i></span>
                            </div>
                            <input type="text" name="search" id="riwayatSearch" class="form-control" placeholder="Cari nama atau NIM..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="riwayatKelas" class="small font-weight-bold text-gray-700">Filter Kelas</label>
                        <select name="kelas_id" id="riwayatKelas" class="form-control form-control-sm">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id }}" {{ (string)$kelasId === (string)$k->id ? 'selected' : '' }}>
                                    {{ $k->kelas }} (Sem. {{ $k->semester }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="riwayatPeriode" class="small font-weight-bold text-gray-700">Filter Periode</label>
                        <select name="periode_id" id="riwayatPeriode" class="form-control form-control-sm">
                            <option value="">-- Semua Periode --</option>
                            @foreach($periodes as $p)
                                <option value="{{ $p->id }}" {{ (string)$periodeId === (string)$p->id ? 'selected' : '' }}>
                                    {{ $p->periode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="riwayatPerPage" class="small font-weight-bold text-gray-700">Per Halaman</label>
                        <select name="per_page" id="riwayatPerPage" class="form-control form-control-sm">
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 Baris</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 Baris</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 Baris</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 Baris</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex" style="gap: 0.35rem;">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1 font-weight-bold">
                            <i class="fas fa-filter mr-1" aria-hidden="true"></i> Saring
                        </button>
                        @if($search || $kelasId || $periodeId || $perPage != 15)
                            <a href="{{ route('admin.riwayat.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Saringan">
                                <i class="fas fa-undo" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Mahasiswa -->
    <div class="card shadow-sm border border-gray-200 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h2 class="h6 m-0 font-weight-bold text-gray-900">
                <i class="fas fa-users text-primary mr-1" aria-hidden="true"></i> Daftar Mahasiswa TRKJ (Total: {{ $mahasiswas->total() }})
            </h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle no-datatable">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 140px;">NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th style="width: 120px;">Kelas</th>
                            <th style="width: 130px;" class="text-center">Total Kelompok</th>
                            <th style="width: 150px;">Status Akun</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mahasiswas as $index => $m)
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted">
                                    {{ $mahasiswas->firstItem() + $index }}
                                </td>
                                <td class="align-middle font-weight-bold text-primary">
                                    {{ $m->nim }}
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-gray-900">{{ $m->nama_mahasiswa }}</div>
                                    <span class="text-muted small">{{ $m->user->email ?? 'Belum terdaftar email' }}</span>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-light border text-dark font-weight-bold">
                                        {{ $m->kelas->kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge {{ $m->kelompoks_count > 0 ? 'badge-info' : 'badge-secondary' }} px-2 py-1">
                                        <i class="fas fa-layer-group mr-1"></i> {{ $m->kelompoks_count }} Kelompok
                                    </span>
                                </td>
                                <td class="align-middle">
                                    @if($m->user_id)
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Terdaftar</span>
                                    @else
                                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> Belum Mendaftar</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <a href="{{ route('admin.riwayat.show', $m) }}" class="btn btn-sm btn-primary font-weight-bold shadow-sm">
                                        <i class="fas fa-eye mr-1"></i> Riwayat PBL
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Tidak ada data mahasiswa ditemukan untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($mahasiswas->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    Menampilkan {{ $mahasiswas->firstItem() }} sampai {{ $mahasiswas->lastItem() }} dari total {{ $mahasiswas->total() }} mahasiswa
                </span>
                <div>
                    {{ $mahasiswas->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
