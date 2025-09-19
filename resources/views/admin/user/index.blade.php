@extends('layout.app')
@section('content')
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Pengguna</h1>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('user.create') }}" class="btn btn-primary btn-icon-split btn-sm">
                                    <span class="icon text-primary-50">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                    <span class="text">Tambah</span>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered small" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama User</th>
                                            <th>Email</th>
                                            <th>Nomor Telp/WA</th>
                                            <th>Username</th>
                                            <th>Password</th>
                                            <th>Role</th>
                                            <th class="text-center align-middle">
                                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data as $i => $u)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $u->nama_user}}</td>
                                            <td>{{ $u->email}}</td>
                                            <td>{{ $u->no_hp}}</td>
                                            <td>{{ $u->username}}</td>
                                            <td>
                                                <form action="{{ route('user.reset-password', $u) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm p-1" style="font-size:0.75rem; line-height:1; color: orange;" title="Reset Password">
                                                        <i class="fas fa-key" aria-hidden="true"></i> Atur Ulang Sandi
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                @php $role = strtolower($u->role ?? ''); @endphp
                                                @switch($role)
                                                    @case('admin')
                                                        <span class="badge badge-primary badge-pill">
                                                            <i class="fas fa-user-shield fa-xs"></i> Admin
                                                        </span>
                                                        @break

                                                    @case('evaluator')
                                                        <span class="badge badge-info badge-pill">
                                                            <i class="fas fa-clipboard-check fa-xs"></i> Evaluator
                                                        </span>
                                                        @break

                                                    @default
                                                        <span class="badge badge-secondary badge-pill">
                                                            <i class="fas fa-user-graduate fa-xs"></i> Mahasiswa
                                                        </span>
                                                @endswitch
                                            </td>
                                            <td class="text-center align-middle">
                                                <a href="{{ route('user.edit', $u) }}" class="btn btn-success btn-circle btn-sm">
                                                    <i class="fas fa-edit fa-sm" alt="Ubah" aria-hidden="true"></i>
                                                </a>
                                                
                                                <form action="{{ route('user.destroy', $u) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-circle btn-sm"
                                                        onclick="var form = this.closest('form'); 
                                                        Swal.fire({ title: 'Hapus?', 
                                                        html: 'Data yang sudah di <strong>HAPUS</strong> tidak dapat dikembalikan? Pastikan data yang dihapus sudah <strong>benar..!</strong>.',
                                                        icon: 'warning', showCancelButton: true, 
                                                        confirmButtonText: 'Ya, hapus!', 
                                                        cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) form.submit(); });">
                                                        <i class="fas fa-trash fa-sm" alt="Hapus" aria-hidden="true"></i>
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
       
