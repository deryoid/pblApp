@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Pengguna</h1>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
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
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Nama User</th>
                            <th>Email</th>
                            <th>Nomor Telp/WA</th>
                            <th>Username</th>
                            <th class="text-center">Password</th>
                            <th class="text-center">Role</th>
                            <th class="text-center align-middle" style="width: 90px;">
                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $u)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle font-weight-bold">{{ $u->nama_user }}</td>
                            <td class="align-middle">{{ $u->email }}</td>
                            <td class="align-middle text-nowrap">{{ $u->no_hp ?? '-' }}</td>
                            <td class="align-middle text-nowrap">{{ $u->username }}</td>
                            <td class="text-center align-middle text-nowrap">
                                <form action="{{ route('user.reset-password', $u) }}" method="POST" class="m-0 d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-warning text-nowrap" title="Reset Password">
                                        <i class="fas fa-key mr-1"></i> Atur Ulang Sandi
                                    </button>
                                </form>
                            </td>
                            <td class="text-center align-middle text-nowrap">
                                @php $role = strtolower($u->role ?? ''); @endphp
                                @switch($role)
                                    @case('admin')
                                        <span class="badge badge-primary badge-pill">
                                            <i class="fas fa-user-shield fa-xs mr-1"></i> Admin
                                        </span>
                                        @break

                                    @case('evaluator')
                                        <span class="badge badge-info badge-pill">
                                            <i class="fas fa-clipboard-check fa-xs mr-1"></i> Evaluator
                                        </span>
                                        @break

                                    @default
                                        <span class="badge badge-secondary badge-pill">
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
                                            html: 'Data yang sudah di <strong>HAPUS</strong> tidak dapat dikembalikan? Pastikan data yang dihapus sudah <strong>benar..!</strong>.',
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
