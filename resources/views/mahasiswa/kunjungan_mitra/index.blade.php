@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 text-gray-800">Kunjungan Mitra</h1>
        <a href="{{ route('mahasiswa.kunjungan.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered small" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Periode</th>
                            <th>Kelompok</th>
                            <th>Perusahaan</th>
                            <th>Tanggal Kunjungan</th>
                            <th>Status</th>
                            <th>Bukti</th>
                            <th>Diinput Oleh</th>
                            <th class="text-center"><i class="fas fa-cogs"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $i => $it)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $it->periode->periode ?? '-' }}</td>
                            <td>{{ $it->kelompok->nama_kelompok ?? '-' }}</td>
                            <td>{{ $it->perusahaan }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($it->tanggal_kunjungan)->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $status = $it->status_kunjungan;
                                    $colors = [
                                        'Sudah dikunjungi'    => 'success',
                                        'Proses Pembicaraan'   => 'warning',
                                        'Tidak ada tanggapan'  => 'secondary',
                                        'Ditolak'              => 'danger',
                                    ];
                                    $badge = $colors[$status] ?? 'light';
                                @endphp
                                <span class="badge badge-{{ $badge }}">{{ $status }}</span>
                            </td>
                            <td>
                                @if($it->bukti_data_url)
                                    <img src="{{ $it->bukti_data_url }}" alt="Bukti" style="height:50px;object-fit:cover;border-radius:4px;">
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $it->user->nama_user ?? '-' }}</td>
                            <td class="text-center">
                                <a class="btn btn-success btn-sm" href="{{ route('mahasiswa.kunjungan.edit', $it) }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('mahasiswa.kunjungan.destroy', $it) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
