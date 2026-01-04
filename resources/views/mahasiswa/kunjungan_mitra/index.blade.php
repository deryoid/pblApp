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
                        @forelse($items as $it)
                        <tr>
                            <td>{{ $items->firstItem() + $loop->index }}</td>
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
                                    <button type="button" class="btn btn-sm btn-info" onclick="viewBukti('{{ $it->uuid }}')" title="Lihat Bukti">
                                        <i class="fas fa-image"></i> Lihat
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $it->user->nama_user ?? '-' }}</td>
                            <td class="text-center">
                                <a class="btn btn-circle btn-success btn-sm" href="{{ route('mahasiswa.kunjungan.edit', $it) }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('mahasiswa.kunjungan.destroy', $it) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-circle btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($items->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Menampilkan {{ $items->firstItem() }} sampai {{ $items->lastItem() }} dari {{ $items->total() }} data
                    </div>
                    {{ $items->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal untuk lihat bukti --}}
<div class="modal fade" id="modalBukti" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Kunjungan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="buktiLoading" class="py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat bukti kunjungan...</p>
                </div>
                <div id="buktiContent" style="display:none;">
                    <div id="buktiInfo" class="mb-3"></div>
                    <img id="buktiImage" src="" alt="Bukti Kunjungan" class="img-fluid" style="max-height: 70vh;">
                </div>
                <div id="buktiError" style="display:none;" class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Gagal memuat bukti kunjungan
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Base URL for bukti API
const buktiBaseUrl = "{{ url('/mahasiswa/kunjungan') }}";

function viewBukti(id) {
    // Reset modal
    document.getElementById('buktiLoading').style.display = 'block';
    document.getElementById('buktiContent').style.display = 'none';
    document.getElementById('buktiError').style.display = 'none';
    document.getElementById('buktiImage').src = '';
    document.getElementById('buktiInfo').innerHTML = '';

    // Show modal
    $('#modalBukti').modal('show');

    // Fetch bukti data
    const url = buktiBaseUrl + '/' + id + '/bukti';
    console.log('Fetching bukti from:', url);

    fetch(url, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success && data.url) {
            document.getElementById('buktiImage').src = data.url;
            document.getElementById('buktiInfo').innerHTML = `
                <strong>${data.perusahaan || '-'}</strong><br>
                <small class="text-muted">${data.tanggal || '-'}</small>
            `;
            document.getElementById('buktiLoading').style.display = 'none';
            document.getElementById('buktiContent').style.display = 'block';
        } else {
            throw new Error(data.message || 'Bukti tidak ditemukan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('buktiLoading').style.display = 'none';
        document.getElementById('buktiError').style.display = 'block';
    });
}
</script>
@endpush
