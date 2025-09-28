@extends('layout.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Penilaian Dosen</h1>
        <div>
            <a href="{{ route('admin.evaluasi.show', $sesi->id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <div class="icon-wrap bg-blue-100">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div>
                        <div class="metric-title">Total Kelompok</div>
                        <div class="metric-value">{{ $kelompoks->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <div class="icon-wrap bg-green-100">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div>
                        <div class="metric-title">Sudah Dinilai</div>
                        <div class="metric-value">{{ $kelompoks->whereNotNull('nilai_dosen')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <div class="icon-wrap bg-yellow-100">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div>
                        <div class="metric-title">Belum Dinilai</div>
                        <div class="metric-value">{{ $kelompoks->whereNull('nilai_dosen')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <div class="icon-wrap bg-indigo-100">
                        <i class="fas fa-chart-line text-info"></i>
                    </div>
                    <div>
                        <div class="metric-title">Rata-rata Nilai</div>
                        <div class="metric-value">{{ $kelompoks->whereNotNull('nilai_dosen')->avg('nilai_dosen') ? round($kelompoks->whereNotNull('nilai_dosen')->avg('nilai_dosen'), 2) : 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari nama kelompok...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="rated">Sudah Dinilai</option>
                        <option value="unrated">Belum Dinilai</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="kelasFilter">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success btn-block" onclick="bulkGrade()">
                        <i class="fas fa-tasks"></i> Penilaian Massal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kelompok - Penilaian Dosen</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="kelompokTable">
                    <thead class="thead-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Informasi Kelompok</th>
                            <th>Kelas</th>
                            <th>Anggota</th>
                            <th>Status</th>
                            <th>Nilai Dosen</th>
                            <th>Detail Nilai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kelompoks as $index => $kelompok)
                        <tr class="kelompok-row"
                            data-status="{{ $kelompok->nilai_dosen ? 'rated' : 'unrated' }}"
                            data-kelas="{{ $kelompok->kelas_id }}"
                            data-search="{{ strtolower($kelompok->nama_kelompok) }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $kelompok->nama_kelompok }}</div>
                                <small class="text-muted">{{ $kelompok->jumlah_proyek }} proyek</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $kelompok->kelas->nama_kelas ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($kelompok->mahasiswas->take(3) as $mhs)
                                        <span class="badge badge-secondary" title="{{ $mhs->nama_mahasiswa }}">
                                            {{ Str::limit($mhs->nama_mahasiswa, 10) }}
                                        </span>
                                    @endforeach
                                    @if($kelompok->mahasiswas->count() > 3)
                                        <span class="badge badge-light">+{{ $kelompok->mahasiswas->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($kelompok->nilai_dosen)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Sudah Dinilai
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-exclamation"></i> Belum Dinilai
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($kelompok->nilai_dosen)
                                    <span class="badge badge-primary badge-pill" style="font-size: 1rem;">
                                        {{ number_format($kelompok->nilai_dosen, 2) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($kelompok->nilai_dosen)
                                    <button class="btn btn-sm btn-outline-info" onclick="viewDetail('{{ $kelompok->id }}')">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-primary" onclick="gradeKelompok('{{ $kelompok->id }}', '{{ $kelompok->nama_kelompok }}')"
                                            title="Beri Nilai">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    @if($kelompok->nilai_dosen)
                                        <button class="btn btn-info" onclick="editNilai('{{ $kelompok->id }}')"
                                                title="Edit Nilai">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="deleteNilai('{{ $kelompok->id }}')"
                                                title="Hapus Nilai">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
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

<!-- Modal Penilaian -->
<div class="modal fade" id="gradeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Penilaian Dosen - <span id="modalTitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="gradeForm" method="POST">
                @csrf
                <input type="hidden" name="kelompok_id" id="kelompokId">

                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Panduan Penilaian:</strong> Berikan nilai untuk setiap kriteria dengan rentang 1-100.
                        Sistem akan menghitung nilai akhir secara otomatis berdasarkan bobot yang telah ditetapkan.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="180">Mahasiswa</th>
                                    <th width="140">Kualitas Hasil<br><small>Bobot: {{ $settings['d_hasil'] ?? 20 }}%</small></th>
                                    <th width="140">Kompleksitas Teknis<br><small>Bobot: {{ $settings['d_teknis'] ?? 20 }}%</small></th>
                                    <th width="140">Kesesuaian Pengguna<br><small>Bobot: {{ $settings['d_user'] ?? 15 }}%</small></th>
                                    <th width="140">Efisiensi Waktu/Biaya<br><small>Bobot: {{ $settings['d_efisiensi'] ?? 15 }}%</small></th>
                                    <th width="140">Dokumentasi & Profesionalisme<br><small>Bobot: {{ $settings['d_dokpro'] ?? 15 }}%</small></th>
                                    <th width="140">Kemandirian & Inisiatif<br><small>Bobot: {{ $settings['d_inisiatif'] ?? 15 }}%</small></th>
                                    <th width="100">Rata-rata</th>
                                    <th width="120">Final<br><small>(Weighted)</small></th>
                                </tr>
                            </thead>
                            <tbody id="gradeTableBody">
                                <!-- Dynamic content -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Nilai -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Penilaian Dosen - <span id="detailTitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detailContent">
                    <!-- Dynamic content -->
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
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('.kelompok-row').filter(function() {
            $(this).toggle($(this).data('search').includes(value));
        });
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        const status = $(this).val();
        if (status === '') {
            $('.kelompok-row').show();
        } else {
            $('.kelompok-row').each(function() {
                $(this).toggle($(this).data('status') === status);
            });
        }
    });

    // Class filter
    $('#kelasFilter').on('change', function() {
        const kelasId = $(this).val();
        if (kelasId === '') {
            $('.kelompok-row').show();
        } else {
            $('.kelompok-row').each(function() {
                $(this).toggle($(this).data('kelas') == kelasId);
            });
        }
    });
});

// Grade kelompok
function gradeKelompok(kelompokId, kelompokName) {
    $('#modalTitle').text(kelompokName);
    $('#kelompokId').val(kelompokId);

    // Load mahasiswa data
    $.get("{{ route('admin.evaluasi.dosen.mahasiswa', ['kelompok'=>'__ID__']) }}".replace('__ID__', kelompokId))
        .done(function(data) {
            let html = '';
            data.mahasiswas.forEach(function(mhs, index) {
                html += `
                    <tr>
                        <td style="vertical-align: middle; background: #f8f9fa; font-weight: 600;">
                            <div>${mhs.nama}</div>
                            <div style="font-size: 0.85rem; color: #6b7280;">${mhs.nim}</div>
                        </td>
                        <td><input type="number" min="1" max="100" class="form-control form-control-sm grade-input" data-mahasiswa="${mhs.id}" data-kriteria="d_hasil"></td>
                        <td><input type="number" min="1" max="100" class="form-control form-control-sm grade-input" data-mahasiswa="${mhs.id}" data-kriteria="d_teknis"></td>
                        <td><input type="number" min="1" max="100" class="form-control form-control-sm grade-input" data-mahasiswa="${mhs.id}" data-kriteria="d_user"></td>
                        <td><input type="number" min="1" max="100" class="form-control form-control-sm grade-input" data-mahasiswa="${mhs.id}" data-kriteria="d_efisiensi"></td>
                        <td><input type="number" min="1" max="100" class="form-control form-control-sm grade-input" data-mahasiswa="${mhs.id}" data-kriteria="d_dokpro"></td>
                        <td><input type="number" min="1" max="100" class="form-control form-control-sm grade-input" data-mahasiswa="${mhs.id}" data-kriteria="d_inisiatif"></td>
                        <td class="text-center"><span class="badge badge-primary average-badge">0</span></td>
                        <td class="text-center"><span class="badge badge-success final-badge">0</span></td>
                    </tr>
                `;
            });
            $('#gradeTableBody').html(html);

            // Add auto-calculate functionality
            $('.grade-input').on('input', function() {
                calculateRow($(this).closest('tr'));
            });
        });

    $('#gradeModal').modal('show');
}

// Calculate row totals
function calculateRow(row) {
    const inputs = row.find('.grade-input');
    let total = 0;
    let count = 0;
    let weightedTotal = 0;

    const weights = {
        'd_hasil': {{ $settings['d_hasil'] ?? 20 }},
        'd_teknis': {{ $settings['d_teknis'] ?? 20 }},
        'd_user': {{ $settings['d_user'] ?? 15 }},
        'd_efisiensi': {{ $settings['d_efisiensi'] ?? 15 }},
        'd_dokpro': {{ $settings['d_dokpro'] ?? 15 }},
        'd_inisiatif': {{ $settings['d_inisiatif'] ?? 15 }}
    };

    inputs.each(function() {
        const val = parseInt($(this).val()) || 0;
        total += val;
        count++;
        const kriteria = $(this).data('kriteria');
        if (weights[kriteria]) {
            weightedTotal += (val * weights[kriteria] / 100);
        }
    });

    const average = count > 0 ? Math.round(total / count) : 0;
    const final = Math.round(weightedTotal);

    row.find('.average-badge').text(average);
    row.find('.final-badge').text(final);
}

// View detail
function viewDetail(kelompokId) {
    $('#detailTitle').text('Loading...');
    $('#detailModal').modal('show');

    $.get("{{ route('admin.evaluasi.dosen.detail', ['kelompok'=>'__ID__']) }}".replace('__ID__', kelompokId))
        .done(function(data) {
            let html = `
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Kualitas Hasil</th>
                                <th>Kompleksitas Teknis</th>
                                <th>Kesesuaian Pengguna</th>
                                <th>Efisiensi Waktu/Biaya</th>
                                <th>Dokumentasi & Profesionalisme</th>
                                <th>Kemandirian & Inisiatif</th>
                                <th>Final</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.nilai.forEach(function(n) {
                html += `
                    <tr>
                        <td><strong>${n.nama}</strong><br><small>${n.nim}</small></td>
                        <td class="text-center">${n.d_hasil || '-'}</td>
                        <td class="text-center">${n.d_teknis || '-'}</td>
                        <td class="text-center">${n.d_user || '-'}</td>
                        <td class="text-center">${n.d_efisiensi || '-'}</td>
                        <td class="text-center">${n.d_dokpro || '-'}</td>
                        <td class="text-center">${n.d_inisiatif || '-'}</td>
                        <td class="text-center"><strong>${n.final}</strong></td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6>Statistik Kelompok</h6>
                                <p class="mb-1">Rata-rata: <strong>${data.stats.average}</strong></p>
                                <p class="mb-1">Tertinggi: <strong>${data.stats.max}</strong></p>
                                <p class="mb-0">Terendah: <strong>${data.stats.min}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h6>Informasi Penilaian</h6>
                                <p class="mb-1">Dosen: <strong>${data.evaluator}</strong></p>
                                <p class="mb-1">Tanggal: <strong>${data.date}</strong></p>
                                <p class="mb-0">Total Nilai: <strong>${data.total}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#detailContent').html(html);
            $('#detailTitle').text(data.kelompok_name);
        });
}

// Bulk grade
function bulkGrade() {
    Swal.fire({
        title: 'Penilaian Massal',
        text: 'Pilih kelompok yang ingin dinilai secara massal',
        icon: 'info',
        html: `
            <div class="text-left">
                <p>Fitur ini memungkinkan Anda untuk:</p>
                <ul>
                    <li>Memilih beberapa kelompok sekaligus</li>
                    <li>Menggunakan template nilai yang sama</li>
                    <li>Menyelesaikan penilaian lebih cepat</li>
                </ul>
                <div class="form-group">
                    <label>Template Nilai (opsional):</label>
                    <select class="form-control" id="bulkTemplate">
                        <option value="">- Pilih Template -</option>
                        <option value="template1">Template A (Sudah Ada)</option>
                        <option value="template2">Template B (Sudah Ada)</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement bulk grading logic
            Swal.fire('Info', 'Fitur penilaian massal akan segera tersedia', 'info');
        }
    });
}

// Edit nilai
function editNilai(kelompokId) {
    // Load existing data and show edit modal
    gradeKelompok(kelompokId, 'Edit Nilai');
    // Additional edit logic here
}

// Delete nilai
function deleteNilai(kelompokId) {
    Swal.fire({
        title: 'Hapus Nilai?',
        text: 'Apakah Anda yakin ingin menghapus nilai penilaian dosen untuk kelompok ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("{{ route('admin.evaluasi.dosen.destroy', ['kelompok'=>'__ID__']) }}".replace('__ID__', kelompokId), {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire('Berhasil', 'Nilai berhasil dihapus', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Swal.fire('Gagal', response.message || 'Gagal menghapus nilai', 'error');
                }
            })
            .fail(function() {
                Swal.fire('Gagal', 'Terjadi kesalahan', 'error');
            });
        }
    });
}

// Form submission
$('#gradeForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const nilaiData = {};

    // Collect nilai data
    $('.grade-input').each(function() {
        const mahasiswaId = $(this).data('mahasiswa');
        const kriteria = $(this).data('kriteria');
        const value = $(this).val();

        if (!nilaiData[mahasiswaId]) {
            nilaiData[mahasiswaId] = {};
        }
        nilaiData[mahasiswaId][kriteria] = value;
    });

    formData.append('nilai', JSON.stringify(nilaiData));

    $.post("{{ route('admin.evaluasi.dosen.store') }}", formData)
        .done(function(response) {
            if (response.success) {
                Swal.fire('Berhasil', 'Nilai berhasil disimpan', 'success');
                $('#gradeModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                Swal.fire('Gagal', response.message || 'Gagal menyimpan nilai', 'error');
            }
        })
        .fail(function() {
            Swal.fire('Gagal', 'Terjadi kesalahan', 'error');
        });
});
</script>
@endpush