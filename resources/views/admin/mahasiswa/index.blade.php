@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Bank Data Mahasiswa</h1>
            <p class="text-muted small mb-0">Master bank data mahasiswa program studi. Nama dan NIM diimport/diinput di sini dan otomatis disesuaikan saat mahasiswa mendaftar akun.</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <span class="badge badge-primary px-2 py-1">Total Bank Data: {{ $data->count() }}</span>
            <span class="badge badge-success px-2 py-1 ml-1">Akun Terhubung: {{ $data->whereNotNull('user_id')->count() }}</span>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap w-100" style="gap: 0.75rem;">
                {{-- Kiri: Tambah --}}
                <div>
                    <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Mahasiswa
                    </a>
                </div>

                {{-- Kanan: Import + Sinkron --}}
                <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                    {{-- Import CSV --}}
                    <a href="{{ route('mahasiswa.import.form') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-file-upload mr-1"></i> Import CSV
                    </a>

                    {{-- Sinkronkan Akun --}}
                    <form action="{{ route('mahasiswa.sync') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Sinkronkan akun dari tabel users">
                            <i class="fas fa-sync-alt mr-1"></i> Sinkronkan Akun
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
                            <th>NIM</th>
                            <th>Nama Mahasiswa (Bank Data)</th>
                            <th>Kelas</th>
                            <th>Akun Pengguna</th>
                            <th class="text-center">Status Akun</th>
                            <th class="text-center">Atur Sandi</th>
                            <th class="text-center align-middle" style="width: 90px;">
                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $m)
                        @php $u = $m->user; @endphp
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle font-weight-bold text-nowrap">{{ $m->nim ?? '-' }}</td>
                            <td class="align-middle font-weight-500">{{ $m->nama_mahasiswa }}</td>
                            <td class="align-middle text-nowrap">
                                @if($m->kelas)
                                    <span class="badge badge-light border">{{ $m->kelas->kelas }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($u)
                                    <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">{{ $u->username }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $u->email }}</div>
                                @else
                                    <span class="text-muted small"><em>Belum terdaftar</em></span>
                                @endif
                            </td>

                            {{-- STATUS AKUN --}}
                            <td class="text-center align-middle text-nowrap">
                                @if($u)
                                    <span class="badge badge-success badge-pill py-1 px-2">
                                        <i class="fas fa-check-circle mr-1"></i> Terhubung
                                    </span>
                                @else
                                    <span class="badge badge-warning badge-pill py-1 px-2">
                                        <i class="fas fa-clock mr-1"></i> Belum Mendaftar
                                    </span>
                                @endif
                            </td>

                            {{-- Reset Password --}}
                            <td class="text-center align-middle text-nowrap">
                                @if($u)
                                <form action="{{ route('user.reset-password', $u) }}" method="POST" class="m-0 d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-warning text-nowrap" title="Reset Password Akun">
                                        <i class="fas fa-key mr-1"></i> Reset
                                    </button>
                                </form>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center align-middle text-nowrap">
                                <div class="table-action-group">
                                    <a href="{{ route('admin.riwayat.show', $m) }}" class="btn btn-sm btn-outline-info btn-action-icon" title="Lihat Riwayat PBL">
                                        <i class="fas fa-history fa-sm" aria-hidden="true"></i>
                                    </a>

                                    <a href="{{ route('mahasiswa.edit', $m) }}" class="btn btn-sm btn-outline-success btn-action-icon" title="Ubah Data">
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
                                            title="Hapus Data">
                                            <i class="fas fa-trash fa-sm" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
