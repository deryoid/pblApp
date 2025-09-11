@extends('layout.app')
@section('content')
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Kelas</h1>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('kelas.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus-circle"></i> Tambah
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered small" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kelas</th>
                                            <th class="text-center align-middle">
                                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data as $i => $k)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $k->kelas}}</td>
                                            <td class="text-center align-middle">
                                                <a href="{{ route('kelas.edit', $k->uuid) }}" class="btn btn-success btn-sm">
                                                    <i class="fas fa-edit fa-sm" alt="Ubah" aria-hidden="true"></i>
                                                </a>
                                                

                                                <form action="{{ route('kelas.destroy', $k->uuid) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="var form = this.closest('form'); 
                                                        Swal.fire({ title: 'Hapus?', 
                                                        text: 'Apakah Anda yakin ingin menghapus data ini?', 
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
       