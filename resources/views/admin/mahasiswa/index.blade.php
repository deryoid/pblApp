@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Mahasiswa</h1>
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
                            <th>Nama Mahasiswa</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th class="text-center">Password</th>
                            <th class="text-center">Role</th>
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
                            <td class="align-middle font-weight-bold text-nowrap">{{ $m->nim }}</td>
                            <td class="align-middle">{{ $m->nama_mahasiswa }}</td>
                            <td class="align-middle">{{ $u->email ?? '-' }}</td>
                            <td class="align-middle text-nowrap">{{ $u->username ?? '-' }}</td>

                            {{-- Reset Password --}}
                            <td class="text-center align-middle text-nowrap">
                                @if($u)
                                <form action="{{ route('user.reset-password', $u) }}" method="POST" class="m-0 d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-warning text-nowrap" title="Reset Password">
                                        <i class="fas fa-key mr-1"></i> Atur Ulang Sandi
                                    </button>
                                </form>
                                @else
                                <span class="badge badge-warning badge-pill">Belum ada akun</span>
                                @endif
                            </td>

                            {{-- ROLE --}}
                            <td class="text-center align-middle text-nowrap">
                                @if($u && $u->role === 'mahasiswa')
                                    <span class="badge badge-secondary badge-pill">
                                        <i class="fas fa-user-graduate fa-xs mr-1"></i> Mahasiswa
                                    </span>
                                @elseif($u && $u->role === 'evaluator')
                                    <span class="badge badge-info badge-pill">
                                        <i class="fas fa-clipboard-check fa-xs mr-1"></i> Evaluator
                                    </span>
                                @elseif($u && $u->role === 'admin')
                                    <span class="badge badge-primary badge-pill">
                                        <i class="fas fa-user-shield fa-xs mr-1"></i> Admin
                                    </span>
                                @else
                                    <span class="badge badge-light badge-pill">-</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center align-middle text-nowrap">
                                <div class="table-action-group">
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
