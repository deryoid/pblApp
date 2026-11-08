@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Pengguna Sistem</h1>
            <p class="text-secondary small mb-0 font-weight-500">Kelola seluruh kredensial akun pengguna aktif (Administrator, Dosen, Evaluator, dan Mahasiswa).</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <div class="badge badge-light border px-3 py-2 text-dark font-weight-600">
                <i class="fas fa-users mr-1 text-primary" aria-hidden="true"></i> Total Pengguna: {{ $data->count() }}
            </div>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow-sm border border-gray-200 mb-4">
        <div class="card-header py-3 bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center w-100">
                <a href="{{ route('user.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Pengguna
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center no-sort" style="width: 45px;">No</th>
                            <th>Nama Pengguna</th>
                            <th>Email</th>
                            <th style="width: 140px;">Nomor Telp / WA</th>
                            <th style="width: 130px;">Username</th>
                            <th class="text-center no-sort" style="width: 120px;">Atur Sandi</th>
                            <th class="text-center" style="width: 110px;">Role</th>
                            <th class="text-center align-middle no-sort" style="width: 90px;">
                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $u)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle font-weight-bold text-dark">{{ $u->nama_user }}</td>
                            <td class="align-middle">{{ $u->email }}</td>
                            <td class="align-middle text-nowrap">
                                @if($u->no_hp && $u->no_hp !== '-')
                                    <span class="text-success font-weight-500"><i class="fas fa-phone mr-1"></i>{{ $u->no_hp }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="align-middle font-weight-bold text-nowrap text-primary">{{ $u->username }}</td>
                            <td class="text-center align-middle text-nowrap">
                                <form action="{{ route('user.reset-password', $u) }}" method="POST" class="m-0 d-inline">
                                    @csrf
                                    <button type="button" class="btn btn-xs btn-outline-warning text-nowrap"
                                        onclick="var form = this.closest('form'); 
                                        Swal.fire({
                                            title: 'Atur Ulang Sandi?',
                                            text: 'Kata sandi pengguna {{ $u->nama_user }} akan diatur ulang menjadi default ({{ $u->username }}@pbl).',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonText: 'Ya, Atur Ulang',
                                            cancelButtonText: 'Batal'
                                        }).then((result) => { if (result.isConfirmed) form.submit(); });"
                                        title="Reset Password">
                                        <i class="fas fa-key mr-1"></i> Atur Ulang
                                    </button>
                                </form>
                            </td>
                            <td class="text-center align-middle text-nowrap">
                                @php $role = strtolower($u->role ?? ''); @endphp
                                @switch($role)
                                    @case('admin')
                                        <span class="badge badge-primary">
                                            <i class="fas fa-user-shield fa-xs mr-1"></i> Admin
                                        </span>
                                        @break

                                    @case('dosen')
                                        <span class="badge badge-success">
                                            <i class="fas fa-chalkboard-teacher fa-xs mr-1"></i> Dosen
                                        </span>
                                        @break

                                    @case('evaluator')
                                        <span class="badge badge-info">
                                            <i class="fas fa-clipboard-check fa-xs mr-1"></i> Evaluator
                                        </span>
                                        @break

                                    @default
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-user-graduate fa-xs mr-1"></i> Mahasiswa
                                        </span>
                                @endswitch
                            </td>
                            <td class="text-center align-middle text-nowrap">
                                <div class="table-action-group">
                                    <a href="{{ route('user.edit', $u) }}" class="btn btn-sm btn-outline-success btn-action-icon" title="Ubah Data">
                                        <i class="fas fa-edit fa-sm" aria-hidden="true"></i>
                                    </a>
                                    
                                    <form action="{{ route('user.destroy', $u) }}" method="POST" class="m-0 p-0 d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                            onclick="var form = this.closest('form'); 
                                            Swal.fire({ title: 'Hapus?', 
                                            html: 'Data pengguna <strong>{{ $u->nama_user }}</strong> yang sudah di-HAPUS tidak dapat dikembalikan.',
                                            icon: 'warning', showCancelButton: true, 
                                            confirmButtonText: 'Ya, hapus!', 
                                            cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) form.submit(); });"
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
