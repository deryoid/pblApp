@extends('layout.app')
@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Periode</h1>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center w-100">
                <a href="{{ route('periode.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Periode
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Periode</th>
                            <th class="text-center">Status</th>
                            <th class="text-center align-middle" style="width: 90px;">
                                <i class="fas fa-cogs fa-sm" aria-hidden="true"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $p)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle font-weight-bold">{{ $p->periode }}</td>
                            <td class="text-center align-middle">
                                @if($p->status_periode === 'Aktif')
                                    <span class="badge badge-success badge-pill">
                                        <i class="fas fa-check-circle mr-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge badge-danger badge-pill">
                                        <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-center align-middle text-nowrap">
                                <div class="table-action-group">
                                    <a href="{{ route('periode.edit', $p->uuid) }}" class="btn btn-sm btn-outline-success btn-action-icon" title="Ubah Data">
                                        <i class="fas fa-edit fa-sm" aria-hidden="true"></i>
                                    </a>

                                    <form action="{{ route('periode.destroy', $p->uuid) }}" method="POST" class="m-0 p-0 d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                            onclick="var form = this.closest('form'); 
                                            Swal.fire({ title: 'Hapus?', 
                                            text: 'Apakah Anda yakin ingin menghapus data ini?', 
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
