@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Mahasiswa</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
            {{-- Kiri: Tambah --}}
            <div class="mb-2">
                <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary btn-icon-split btn-sm">
                    <span class="icon text-primary-50"><i class="fas fa-plus"></i></span>
                    <span class="text">Tambah</span>
                </a>
            </div>

            {{-- Kanan: Import + Sinkron --}}
            <div class="mb-2 d-flex align-items-center">
                {{-- Import CSV (menuju form import/preview) --}}
                <a href="{{ route('mahasiswa.import.form') }}" class="btn btn-secondary btn-icon-split btn-sm mr-2">
                    <span class="icon text-primary-50"><i class="fas fa-file-upload fa-xs"></i></span>
                    <span class="text">Import CSV</span>
                </a>

                {{-- Sinkronkan Akun --}}
                <form action="{{ route('mahasiswa.sync') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-icon-split btn-sm" title="Sinkronkan akun dari tabel users">
                        <span class="icon text-primary-50"><i class="fas fa-sync-alt fa-xs"></i></span>
                        <span class="text">Sinkronkan Akun</span>
                    </button>
                </form>
            </div>
        </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Role</th>
                            <th class="text-center align-middle">
                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $m)
                        @php $u = $m->user; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $m->nim }}</td>
                            <td>{{ $m->nama_mahasiswa }}</td>
                            <td>{{ $u->email ?? '-' }}</td>
                            <td>{{ $u->username ?? '-' }}</td>

                            {{-- Reset Password --}}
                            <td class="text-center">
                                @if($u)
                                <form action="{{ route('user.reset-password', $u) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm p-1" style="font-size:0.75rem; line-height:1; color: orange;" title="Reset Password">
                                        <i class="fas fa-key" aria-hidden="true"></i> Atur Ulang Sandi
                                    </button>
                                </form>
                                @else
                                <span class="badge badge-warning badge-pill">Akun belum dibuat</span>
                                @endif
                            </td>

                            {{-- ROLE (harusnya selalu 'mahasiswa') --}}
                            <td class="text-center">
                                @if($u && $u->role === 'mahasiswa')
                                    <span class="badge badge-secondary badge-pill">
                                        <i class="fas fa-user-graduate fa-xs"></i> Mahasiswa
                                    </span>
                                @elseif($u && $u->role === 'evaluator')
                                    <span class="badge badge-info badge-pill">
                                        <i class="fas fa-clipboard-check fa-xs"></i> Evaluator
                                    </span>
                                @elseif($u && $u->role === 'admin')
                                    <span class="badge badge-primary badge-pill">
                                        <i class="fas fa-user-shield fa-xs"></i> Admin
                                    </span>
                                @else
                                    <span class="badge badge-light badge-pill">-</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center align-middle">
                                {{-- Edit Mahasiswa (pakai UUID berkat getRouteKeyName) --}}
                                <a href="{{ route('mahasiswa.edit', $m) }}" class="btn btn-success btn-circle btn-sm" title="Ubah">
                                    <i class="fas fa-edit fa-sm" aria-hidden="true"></i>
                                </a>

                                {{-- Hapus Mahasiswa (+ otomatis hapus user terkait di controller) --}}
                                <form action="{{ route('mahasiswa.destroy', $m) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-circle btn-sm"
                                        onclick="var form = this.closest('form');
                                        Swal.fire({
                                            title: 'Hapus?',
                                            html: 'Data yang sudah di <strong>HAPUS</strong> tidak dapat dikembalikan.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonText: 'Ya, hapus!',
                                            cancelButtonText: 'Batal'
                                        }).then((result) => { if (result.isConfirmed) form.submit(); });"
                                        title="Hapus">
                                        <i class="fas fa-trash fa-sm" aria-hidden="true"></i>
                                    </button>
                                </form>
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
