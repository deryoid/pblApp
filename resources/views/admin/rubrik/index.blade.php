@extends('layout.app')

@section('title', 'Rubrik Penilaian Dinamis - SIMEP Politala')

@section('content')
<div class="container-fluid px-3 px-md-4">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                <i class="fas fa-clipboard-check text-primary mr-2"></i>Rubrik Penilaian Dinamis
            </h1>
            <p class="text-muted small mb-0">
                Matriks kriteria dan deskriptor capaian penilaian proyek PBL berdasarkan acuan rubrik 8 skala nilai (A s/d E).
            </p>
        </div>
        <div class="mt-3 mt-sm-0 d-flex flex-wrap" style="gap: 0.5rem;">
            <button type="button" class="btn btn-outline-primary btn-sm shadow-sm" data-toggle="modal" data-target="#simulatorModal">
                <i class="fas fa-calculator mr-1"></i> Simulator Penilaian
            </button>
            <a href="{{ route('admin.evaluasi.settings') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-sliders-h mr-1"></i> Pengaturan Bobot
            </a>
        </div>
    </div>

    <!-- Kategori Cards -->
    <div class="row mb-4">
        @foreach($categories as $cat)
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm rounded-xl h-100" style="border-left: 4px solid var(--primary, #4e73df) !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge badge-light border text-primary font-weight-bold px-2 py-1">
                                Bobot {{ (int)$cat->bobot }}%
                            </span>
                            <span class="text-xs text-muted text-uppercase font-weight-bold">Kategori {{ $cat->urutan }}</span>
                        </div>
                        <h6 class="font-weight-bold text-dark mb-1">{{ $cat->nama_kategori }}</h6>
                        <p class="text-muted text-xs mb-0" style="line-height: 1.4;">
                            {{ $cat->deskripsi }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Matriks Rubrik Table Card -->
    <div class="card shadow-sm border-0 rounded-xl mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 0.5rem;">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary-subtle text-primary p-2 mr-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(78,115,223,0.1);">
                    <i class="fas fa-table"></i>
                </div>
                <div>
                    <h6 class="m-0 font-weight-bold text-dark">Matriks Standar Rubrik Penilaian Proyek</h6>
                    <small class="text-muted">Klik pada deskripsi rubrik untuk melakukan pembaruan narasi deskriptor</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap: 0.5rem;">
                <span class="badge badge-pill badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Excel Sync Ready</span>
                <span class="badge badge-pill badge-info px-2 py-1">8 Jenjang Nilai</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 align-middle" style="font-size: 0.875rem;">
                    <thead class="bg-light text-center text-dark font-weight-bold">
                        <tr>
                            <th style="width: 140px; min-width: 140px; vertical-align: middle;">Nilai Huruf</th>
                            <th style="width: 120px; min-width: 120px; vertical-align: middle;">Rentang Nilai</th>
                            @foreach($categories as $cat)
                                <th style="min-width: 220px; vertical-align: middle;">
                                    <div class="font-weight-bold">{{ $cat->nama_kategori }}</div>
                                    <span class="badge badge-light border text-primary small font-weight-normal mt-1">Bobot: {{ (int)$cat->bobot }}%</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrix as $row)
                            <tr>
                                <!-- Nilai Huruf -->
                                <td class="text-center align-middle font-weight-bold bg-white">
                                    <span class="badge {{ $row['badge'] }} px-3 py-2 font-weight-bold" style="font-size: 1rem; border-radius: 8px;">
                                        {{ $row['grade'] }}
                                    </span>
                                </td>

                                <!-- Rentang Nilai -->
                                <td class="text-center align-middle bg-white">
                                    <span class="font-weight-bold text-gray-800" style="font-size: 0.95rem;">
                                        {{ $row['range'] }}
                                    </span>
                                </td>

                                <!-- Deskriptor Kategori -->
                                @foreach($categories as $cat)
                                    @php
                                        $desc = $row['descriptors'][$cat->kode] ?? null;
                                        $descId = $desc['id'] ?? null;
                                        $descText = $desc['text'] ?? '—';
                                    @endphp
                                    <td class="align-middle descriptor-cell" style="vertical-align: top; background: #fff; position: relative;">
                                        <div class="d-flex flex-column justify-content-between h-100">
                                            <p class="mb-2 text-gray-800 descriptor-text" id="desc-text-{{ $descId }}" style="line-height: 1.5;">
                                                {{ $descText }}
                                            </p>
                                            @if($descId && Auth::user()->role === 'admin')
                                                <div class="text-right mt-auto">
                                                    <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 rounded-pill shadow-none"
                                                            onclick="openEditModal({{ $descId }}, '{{ $row['grade'] }}', '{{ addslashes($cat->nama_kategori) }}', {{ json_encode($descText) }})"
                                                            style="font-size: 0.725rem;" title="Edit narasi deskripsi">
                                                        <i class="fas fa-edit mr-1"></i>Edit
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Acuan Panduan & Penjelasan Rubrik -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 rounded-xl h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-info-circle text-info mr-2"></i>Pedoman Konversi Nilai Huruf
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Sistem penilaian rubrik proyek SIMEP Politala menerapkan pembagian rentang berbasis mutu capaian luaran kerja tim mahasiswa:
                    </p>
                    <div class="row g-2">
                        @foreach($tiers as $tier)
                            <div class="col-sm-6 mb-2">
                                <div class="d-flex align-items-center p-2 rounded border bg-light">
                                    <span class="badge {{ $tier['badge'] }} px-2 py-1 mr-2 font-weight-bold" style="min-width: 36px; text-align: center;">
                                        {{ $tier['grade'] }}
                                    </span>
                                    <div class="small">
                                        <span class="font-weight-bold text-dark">{{ $tier['range'] }}</span>
                                        <div class="text-muted text-xs">
                                            @if($tier['grade'] === 'A') Sangat Memuaskan / Luar Biasa
                                            @elseif($tier['grade'] === 'B+') Baik Sekali / Di Atas Standar
                                            @elseif($tier['grade'] === 'B') Baik / Standar Tercapai
                                            @elseif($tier['grade'] === 'C+') Cukup Baik
                                            @elseif($tier['grade'] === 'C') Cukup / Minimal
                                            @elseif($tier['grade'] === 'D+') Kurang / Perlu Bimbingan
                                            @elseif($tier['grade'] === 'D') Sangat Kurang
                                            @else Gagal / Tidak Mengumpulkan
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 rounded-xl h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-lightbulb text-warning mr-2"></i>Penerapan dalam Penilaian PBL
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="rounded-circle bg-primary text-white p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem;">
                            1
                        </div>
                        <div>
                            <h6 class="font-weight-bold text-dark mb-1">Penilaian Capaian Proyek oleh Dosen</h6>
                            <p class="text-muted small mb-0">
                                Dosen pengampu dan penilai proyek menilai produk berdasarkan 4 pilar (Relevansi Konsep, Kualitas Produk, Kreativitas, Estetika) dengan bobot masing-masing 25%.
                            </p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <div class="rounded-circle bg-success text-white p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem;">
                            2
                        </div>
                        <div>
                            <h6 class="font-weight-bold text-dark mb-1">Kalkulasi Otomatis Skala Mutu</h6>
                            <p class="text-muted small mb-0">
                                Nilai angka (0–100) langsung dipetakan ke predikat huruf (A hingga E) secara akurat sesuai ambang batas Excel (A: 80+, B+: 75+, B: 70+, C+: 65+, C: 60+, D+: 50+, D: 40+).
                            </p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle bg-info text-white p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem;">
                            3
                        </div>
                        <div>
                            <h6 class="font-weight-bold text-dark mb-1">Transparansi Transkrip Mahasiswa</h6>
                            <p class="text-muted small mb-0">
                                Deskriptor capaian dapat dilihat mahasiswa pada transkrip sebagai umpan balik konstruktif terhadap luaran PBL yang telah dikerjakan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Edit Deskriptor -->
<div class="modal fade" id="editDeskriptorModal" tabindex="-1" role="dialog" aria-labelledby="editDeskriptorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-xl">
            <form id="editDeskriptorForm" method="POST" onsubmit="handleDescriptorSubmit(event)">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="editDeskriptorModalLabel">
                        <i class="fas fa-edit text-primary mr-2"></i>Edit Deskripsi Rubrik
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <span id="modalGradeBadge" class="badge badge-success px-3 py-1 font-weight-bold mr-2" style="font-size: 0.9rem;">
                            A
                        </span>
                        <div>
                            <strong id="modalKategoriName" class="text-dark d-block">Relevansi & Konsep</strong>
                            <small class="text-muted">Jenjang Nilai Huruf</small>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="deskripsiInput" class="font-weight-bold small text-dark">Narasi Kriteria / Deskriptor Capaian:</label>
                        <textarea class="form-control" id="deskripsiInput" name="deskripsi" rows="4" required maxlength="1000" placeholder="Tuliskan deskripsi kriteria rubrik..."></textarea>
                        <small class="form-text text-muted">Deskripsi ini menjadi panduan evaluator dalam memberikan skor kepada mahasiswa.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSaveDescriptor" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Simulator Penilaian -->
<div class="modal fade" id="simulatorModal" tabindex="-1" role="dialog" aria-labelledby="simulatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow rounded-xl">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="simulatorModalLabel">
                    <i class="fas fa-calculator text-primary mr-2"></i>Simulator Penilaian Rubrik Dinamis
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-4">
                    Gunakan simulator interaktif ini untuk menguji skor setiap kategori rubrik dan melihat hasil konversi nilai huruf serta narasi deskriptor yang cocok.
                </p>

                <div class="row">
                    <div class="col-md-7">
                        @foreach($categories as $cat)
                            <div class="form-group mb-3 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="font-weight-bold text-dark small mb-0">{{ $cat->nama_kategori }} ({{ (int)$cat->bobot }}%)</label>
                                    <span class="badge badge-primary font-weight-bold" id="sim-val-{{ $cat->kode }}">80</span>
                                </div>
                                <input type="range" class="custom-range sim-slider" id="slider-{{ $cat->kode }}" data-kode="{{ $cat->kode }}" min="0" max="100" value="80" oninput="updateSimulator()">
                            </div>
                        @endforeach
                    </div>

                    <div class="col-md-5">
                        <div class="p-3 bg-light rounded-xl border text-center h-100 d-flex flex-column justify-content-center">
                            <span class="text-xs text-uppercase font-weight-bold text-muted mb-1">Skor Akhir Rubrik</span>
                            <h1 class="display-4 font-weight-bold text-primary mb-2" id="simResultScore">80.0</h1>
                            <div class="mb-2">
                                <span class="badge badge-success px-3 py-2 font-weight-bold" id="simResultBadge" style="font-size: 1.25rem;">
                                    A
                                </span>
                            </div>
                            <div class="text-muted small" id="simResultRange">Rentang: 80 – 100</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentEditingId = null;

function openEditModal(id, grade, kategoriName, text) {
    currentEditingId = id;
    const form = document.getElementById('editDeskriptorForm');
    form.action = "{{ url('admin/rubrik/skala') }}/" + id;

    document.getElementById('modalGradeBadge').textContent = grade;
    document.getElementById('modalGradeBadge').className = 'badge px-3 py-1 font-weight-bold mr-2 ' + getBadgeClass(grade);
    document.getElementById('modalKategoriName').textContent = kategoriName;
    document.getElementById('deskripsiInput').value = text || '';

    $('#editDeskriptorModal').modal('show');
}

function getBadgeClass(grade) {
    switch (grade) {
        case 'A': return 'badge-success';
        case 'B+': return 'badge-primary';
        case 'B': return 'badge-info';
        case 'C+':
        case 'C': return 'badge-warning';
        case 'D+':
        case 'D': return 'badge-danger';
        default: return 'badge-secondary';
    }
}

async function handleDescriptorSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveDescriptor');
    const input = document.getElementById('deskripsiInput');
    const newText = input.value.trim();

    if (!newText) {
        Swal.fire('Peringatan', 'Deskripsi tidak boleh kosong.', 'warning');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

    try {
        const response = await fetch(e.target.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                _method: 'PUT',
                deskripsi: newText
            })
        });

        const data = await response.json();
        if (response.ok && data.success) {
            const textEl = document.getElementById('desc-text-' + currentEditingId);
            if (textEl) {
                textEl.textContent = newText;
            }
            $('#editDeskriptorModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message || 'Deskripsi rubrik berhasil diperbarui.',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            throw new Error(data.message || 'Gagal menyimpan perubahan.');
        }
    } catch (err) {
        Swal.fire('Error', err.message || 'Terjadi kesalahan sistem.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Perubahan';
    }
}

// Simulator calculation
function updateSimulator() {
    const sliders = document.querySelectorAll('.sim-slider');
    let totalScore = 0;
    let count = 0;

    sliders.forEach(s => {
        const val = parseFloat(s.value) || 0;
        const kode = s.getAttribute('data-kode');
        const badge = document.getElementById('sim-val-' + kode);
        if (badge) badge.textContent = Math.round(val);
        totalScore += val * 0.25; // 25% each
        count++;
    });

    const finalScore = count ? (totalScore).toFixed(1) : '0.0';
    document.getElementById('simResultScore').textContent = finalScore;

    const num = parseFloat(finalScore);
    let grade = 'E';
    let range = '0 – 39';
    let badgeClass = 'badge-secondary';

    if (num >= 80) {
        grade = 'A';
        range = '80 – 100';
        badgeClass = 'badge-success';
    } else if (num >= 75) {
        grade = 'B+';
        range = '75 – 79';
        badgeClass = 'badge-primary';
    } else if (num >= 70) {
        grade = 'B';
        range = '70 – 74';
        badgeClass = 'badge-info';
    } else if (num >= 65) {
        grade = 'C+';
        range = '65 – 69';
        badgeClass = 'badge-warning';
    } else if (num >= 60) {
        grade = 'C';
        range = '60 – 64';
        badgeClass = 'badge-warning';
    } else if (num >= 50) {
        grade = 'D+';
        range = '50 – 59';
        badgeClass = 'badge-danger';
    } else if (num >= 40) {
        grade = 'D';
        range = '40 – 49';
        badgeClass = 'badge-danger';
    }

    const badgeEl = document.getElementById('simResultBadge');
    badgeEl.textContent = grade;
    badgeEl.className = 'badge px-3 py-2 font-weight-bold ' + badgeClass;
    document.getElementById('simResultRange').textContent = 'Rentang: ' + range;
}
</script>
@endpush
