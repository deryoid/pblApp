@extends('layout.app')
@section('content')
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Periode</h1>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                 <a href="{{ route('periode.create') }}" class="btn btn-primary btn-icon-split btn-sm">
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
                                            <th>Periode</th>
                                            <th>Status</th>
                                            <th class="text-center align-middle">
                                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data as $i => $p)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $p->periode}}</td>
                                            <td class="text-center">
                                                    @if($p->status_periode === 'Aktif')
                                                    <b style="color: green">    
                                                    <i class="fas fa-check-square" aria-hidden="true"></i> 
                                                         Aktif
                                                    </b>
                                                    @else
                                                    <b style="color: red">    
                                                    <i class="fas fa-times-circle" aria-hidden="true"></i> 
                                                    Tidak Aktif
                                                    </b>
                                                    @endif
                                                </td>


                                            <td class="text-center align-middle">
                                                <a href="{{ route('periode.edit', $p->uuid) }}" class="btn btn-success btn-circle btn-sm">
                                                    <i class="fas fa-edit fa-sm" alt="Ubah" aria-hidden="true"></i>
                                                </a>
                                                

                                                <form action="{{ route('periode.destroy', $p->uuid) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-circle btn-sm"
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
       