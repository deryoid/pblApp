@extends('layout.app')
@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Kelompok</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                @if($periodeAktif)
                    <span class="badge badge-info">
                        Periode Aktif: {{ $periodeAktif->periode }}
                    </span>
                @else
                    <span class="badge badge-warning">
                        Tidak ada periode aktif
                    </span>
                @endif
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Periode</th>
                            <th>Nama Kelompok</th>
                            <th>Ketua Kelompok</th>
                            <th>Jumlah Anggota</th>
                            <th>Status Evaluasi</th>
                            <th class="text-center align-middle"><i class="fas fa-cogs fa-sm"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelompoks as $kelompok)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $kelompok->periode->periode ?? '-' }}</td>
                            <td>{{ $kelompok->nama_kelompok }}</td>
                            <td>{{ $kelompok->ketua_nama ?? '-' }}</td>
                            <td>{{ $kelompok->mahasiswas->count() }}</td>
                            <td class="text-center">
                                <span class="badge {{ $kelompok->evaluation_status_badge }}">
                                    {{ $kelompok->evaluation_status }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('evaluator.kelompok.show', $kelompok->uuid) }}" class="btn btn-info btn-circle btn-sm" title="Detail">
                                    <i class="fas fa-eye fa-sm"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection