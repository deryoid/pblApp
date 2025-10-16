@extends('layout.public')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-star text-primary mr-2" aria-hidden="true"></i>
            Data Penilaian Mitra Semua Kelompok
        </h1>
        <small class="text-muted">
            <i class="fas fa-info-circle mr-1"></i>
            Menampilkan semua data penilaian mitra dari semua proyek
        </small>
    </div>

    <!-- Penilaian Mitra Card -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list mr-2"></i>Daftar Penilaian Mitra
            </h6>
        </div>
        <div class="card-body">
            @if($evaluations->count() > 0)
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Proyek</th>
                                <th>Kelompok</th>
                                <th>Mahasiswa</th>
                                <th>Komunikasi & Sikap</th>
                                <th>Hasil Pekerjaan</th>
                                <th>Nilai Akhir</th>
                                <th>Grade</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($evaluations as $eval)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>
                                        <strong>{{ $eval->projectCard->title ?? 'Tidak diketahui' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            @if($eval->projectCard->nama_mitra)
                                                <i class="fas fa-building"></i> {{ $eval->projectCard->nama_mitra }}
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        {{ $eval->kelompok->nama_kelompok ?? 'Tidak diketahui' }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($eval->mahasiswa && $eval->mahasiswa->user)
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($eval->mahasiswa->nama_mahasiswa ?? $eval->mahasiswa->user->nama_user ?? 'Unknown') }}&background=random&size=32"
                                                     alt="{{ $eval->mahasiswa->nama_mahasiswa }}"
                                                     class="rounded-circle me-2"
                                                     width="32" height="32">
                                                <div>
                                                    <strong>{{ $eval->mahasiswa->nama_mahasiswa ?? $eval->mahasiswa->user->nama_user ?? 'Tidak diketahui' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $eval->mahasiswa->nim ?? 'N/A' }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">Mahasiswa tidak ditemukan</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">
                                            {{ $eval->m_kehadiran ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">
                                            {{ $eval->m_presentasi ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ number_format($eval->nilai_akhir ?? 0, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $eval->grade == 'A' ? 'success' : ($eval->grade == 'B' ? 'primary' : ($eval->grade == 'C' ? 'warning' : 'danger')) }}">
                                            {{ $eval->grade ?? 'E' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $eval->created_at->format('d M Y H:i') }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        Menampilkan {{ $evaluations->firstItem() }} sampai {{ $evaluations->lastItem() }} dari {{ $evaluations->total() }} data
                    </small>
                    {{ $evaluations->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-star fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Belum Ada Data Penilaian Mitra</h5>
                    <p class="text-gray-500">Belum ada penilaian mitra yang dilakukan untuk proyek manapun.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-home mr-2"></i>Kembali ke Beranda
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection