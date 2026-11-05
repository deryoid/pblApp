@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Bank Data Mahasiswa</h1>
            <p class="text-secondary small mb-0 font-weight-500">Master bank data mahasiswa program studi. Data dapat diimport via Excel/CSV dan disesuaikan langsung oleh mahasiswa.</p>
        </div>
        <div class="mt-2 mt-sm-0 d-flex flex-wrap" style="gap: 0.5rem;">
            <div class="badge badge-light border px-2.5 py-1.5 text-dark font-weight-600">
                <i class="fas fa-user-graduate mr-1 text-primary" aria-hidden="true"></i> Total Data: {{ $data->count() }}
            </div>
            <div class="badge-status-linked">
                <i class="fas fa-check-circle mr-1" aria-hidden="true"></i> Terhubung: {{ $data->whereNotNull('user_id')->count() }}
            </div>
        </div>
    </div>

    <div class="card shadow-sm border border-gray-200 mb-4">
        <div class="card-header py-3 bg-white border-bottom">
            <div class="d-flex align-items-center justify-content-between flex-wrap w-100" style="gap: 0.75rem;">
                {{-- Kiri: Tambah --}}
                <div>
                    <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1" aria-hidden="true"></i> Tambah Mahasiswa
                    </a>
                </div>

                {{-- Kanan: Import + Sinkron --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                    {{-- Import Excel / CSV --}}
                    <a href="{{ route('mahasiswa.import.form') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Import Excel / CSV
                    </a>

                    {{-- Sinkronkan Akun --}}
                    <form action="{{ route('mahasiswa.sync') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Sinkronkan akun dari tabel users" aria-label="Sinkronkan akun mahasiswa dari tabel pengguna">
                            <i class="fas fa-sync-alt mr-1" aria-hidden="true"></i> Sinkronkan Akun
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Angkatan</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa (Bank Data)</th>
                            <th>Kelas</th>
                            <th>Akun Pengguna</th>
                            <th class="text-center">Status Akun</th>
                            <th class="text-center">Atur Sandi</th>
                            <th class="text-center align-middle" style="width: 120px;">
                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $i => $m)
                        @php $u = $m->user; @endphp
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle text-nowrap text-center font-weight-bold">
                                {{ $m->angkatan ?? '-' }}
                            </td>
                            <td class="align-middle font-weight-bold text-nowrap">{{ $m->nim ?? '-' }}</td>
                            <td class="align-middle font-weight-500">
                                <div class="text-dark font-weight-600">{{ $m->nama_mahasiswa }}</div>
                                @if($m->no_hp || $m->email_kampus)
                                    <div class="text-secondary font-weight-500 mt-0.5" style="font-size: 0.78rem;">
                                        @if($m->no_hp)<span class="mr-2 text-nowrap"><i class="fas fa-phone fa-xs text-success mr-1" aria-hidden="true"></i>{{ $m->no_hp }}</span>@endif
                                        @if($m->email_kampus)<span class="text-nowrap"><i class="fas fa-envelope fa-xs text-primary mr-1" aria-hidden="true"></i>{{ $m->email_kampus }}</span>@endif
                                    </div>
                                @endif
                            </td>
                            <td class="align-middle text-nowrap">
                                @if($m->kelas)
                                    <span class="badge badge-light border text-dark">{{ $m->kelas->kelas }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($u)
                                    <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">{{ $u->username }}</div>
                                    <div class="text-secondary font-weight-500" style="font-size: 0.775rem;">{{ $u->email }}</div>
                                @else
                                    <span class="text-muted small"><em>Belum terdaftar</em></span>
                                @endif
                            </td>

                            {{-- STATUS AKUN --}}
                            <td class="text-center align-middle text-nowrap">
                                @if($u)
                                    <span class="badge-status-linked">
                                        <i class="fas fa-check-circle mr-1" aria-hidden="true"></i> Terhubung
                                    </span>
                                @else
                                    <span class="badge-status-unlinked">
                                        <i class="fas fa-clock mr-1" aria-hidden="true"></i> Belum Mendaftar
                                    </span>
                                @endif
                            </td>

                            {{-- Reset Password --}}
                            <td class="text-center align-middle text-nowrap">
                                @if($u)
                                <form action="{{ route('user.reset-password', $u) }}" method="POST" class="m-0 d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-warning text-nowrap" aria-label="Reset kata sandi akun {{ $m->nama_mahasiswa }}" title="Reset Password Akun">
                                        <i class="fas fa-key mr-1" aria-hidden="true"></i> Reset
                                    </button>
                                </form>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center align-middle text-nowrap">
                                <div class="table-action-group">
                                    {{-- Modal Detail Biodata --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action-icon" data-toggle="modal" data-target="#modalDetail{{ $m->id }}" aria-label="Lihat detail biodata {{ $m->nama_mahasiswa }}" title="Lihat Detail Biodata">
                                        <i class="fas fa-eye fa-sm" aria-hidden="true"></i>
                                    </button>

                                    <a href="{{ route('admin.riwayat.show', $m) }}" class="btn btn-sm btn-outline-info btn-action-icon" aria-label="Lihat riwayat PBL {{ $m->nama_mahasiswa }}" title="Lihat Riwayat PBL">
                                        <i class="fas fa-history fa-sm" aria-hidden="true"></i>
                                    </a>

                                    <a href="{{ route('mahasiswa.edit', $m) }}" class="btn btn-sm btn-outline-success btn-action-icon" aria-label="Ubah data mahasiswa {{ $m->nama_mahasiswa }}" title="Ubah Data">
                                        <i class="fas fa-edit fa-sm" aria-hidden="true"></i>
                                    </a>

                                    <form action="{{ route('mahasiswa.destroy', $m) }}" method="POST" class="m-0 p-0 d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                            onclick="var form = this.closest('form');
                                            Swal.fire({
                                                title: 'Hapus?',
                                                html: 'Data yang sudah di <strong>HAPUS</strong> tidak dapat dikembalikan.',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonText: 'Ya, hapus!',
                                                cancelButtonText: 'Batal'
                                            }).then((result) => { if (result.isConfirmed) form.submit(); });"
                                            aria-label="Hapus data mahasiswa {{ $m->nama_mahasiswa }}"
                                            title="Hapus Data">
                                            <i class="fas fa-trash fa-sm" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>

                                {{-- MODAL DETAIL BIODATA --}}
                                <div class="modal fade" id="modalDetail{{ $m->id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $m->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                        <div class="modal-content text-left">
                                            <div class="modal-header bg-light">
                                                <h2 class="modal-title font-weight-bold h5 mb-0" id="modalLabel{{ $m->id }}">
                                                    <i class="fas fa-id-card text-primary mr-2" aria-hidden="true"></i>Detail Biodata Mahasiswa
                                                </h2>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup dialog detail">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <strong class="text-muted d-block small">Nama Mahasiswa</strong>
                                                        <span class="font-weight-bold h6 text-dark">{{ $m->nama_mahasiswa }}</span>
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <strong class="text-muted d-block small">NIM</strong>
                                                        <span class="font-weight-bold text-dark">{{ $m->nim ?? '-' }}</span>
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <strong class="text-muted d-block small">Tahun Angkatan</strong>
                                                        <span class="badge badge-info px-2 py-1">{{ $m->angkatan ?? '-' }}</span>
                                                    </div>
                                                </div>

                                                <hr class="my-2">

                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <strong class="text-muted d-block small">Kelas Akademik</strong>
                                                        <span>{{ $m->kelas->kelas ?? 'Belum ditentukan' }}</span>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <strong class="text-muted d-block small">NIK (Kependudukan)</strong>
                                                        <span>{{ $m->nik ?? '-' }}</span>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <strong class="text-muted d-block small">Jenis Kelamin</strong>
                                                        <span>{{ $m->jenis_kelamin_text }}</span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <strong class="text-muted d-block small">Agama</strong>
                                                        <span>{{ $m->agama ?? '-' }}</span>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <strong class="text-muted d-block small">Tempat, Tanggal Lahir</strong>
                                                        <span>
                                                            {{ $m->tempat_lahir ?? '-' }},
                                                            {{ $m->tanggal_lahir ? $m->tanggal_lahir->format('d/m/Y') : '-' }}
                                                        </span>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <strong class="text-muted d-block small">Nama Ibu Kandung</strong>
                                                        <span>{{ $m->nama_ibu ?? '-' }}</span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <strong class="text-muted d-block small">Nomor Telepon / WhatsApp</strong>
                                                        <span>{{ $m->no_hp ?? ($u?->no_hp ?? '-') }}</span>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <strong class="text-muted d-block small">Email Kampus</strong>
                                                        <span>{{ $m->email_kampus ?? ($u?->email ?? '-') }}</span>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <strong class="text-muted d-block small">Alamat Lengkap / Domisili</strong>
                                                    <span>{{ $m->alamat ?? '-' }}</span>
                                                </div>

                                                <div class="alert alert-light border mb-0 small">
                                                    <i class="fas fa-user-circle mr-1 text-primary"></i>
                                                    Akun Sistem: <strong>{{ $u ? $u->username.' ('.$u->email.')' : 'Belum Terdaftar / Belum Diklaim' }}</strong>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light py-2">
                                                <a href="{{ route('mahasiswa.edit', $m) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit mr-1"></i> Edit Mahasiswa
                                                </a>
                                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="py-3">
                                    <i class="fas fa-user-graduate fa-3x text-gray-300 mb-3" aria-hidden="true"></i>
                                    <h2 class="font-weight-bold text-gray-700 h6 mb-1">Belum Ada Data Mahasiswa</h2>
                                    <p class="small text-muted mb-3">Mulai tambahkan master mahasiswa atau import berkas Excel / CSV.</p>
                                    <div class="d-inline-flex flex-wrap" style="gap: 0.5rem;">
                                        <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus mr-1" aria-hidden="true"></i> Tambah Mahasiswa
                                        </a>
                                        <a href="{{ route('mahasiswa.import.form') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Import Excel / CSV
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
