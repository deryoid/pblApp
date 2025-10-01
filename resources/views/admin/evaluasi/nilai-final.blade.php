@extends('layout.app')

@section('title', 'Nilai Final Mahasiswa')

@section('content')
<div class="container-fluid">
    @push('styles')
    <style>
        .nilai-card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .nilai-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .info-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
        }
        .calculation-breakdown {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #007bff;
        }
        .ap-detail-item {
            border-left: 3px solid #17a2b8;
            background: #f8f9fa;
            transition: background-color 0.2s;
        }
        .ap-detail-item:hover {
            background: #e9ecef;
        }
        .progress-indicator {
            height: 6px;
            border-radius: 3px;
            background: #e9ecef;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
    </style>
    @endpush
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Nilai Final Mahasiswa
        </h1>
        <div>
            <a href="{{ route('admin.evaluasi.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Card Arahan Penilaian -->
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-graduation-cap mr-2"></i>Arahan Penilaian Project Based Learning
                    </h6>
                    <button class="btn btn-sm btn-outline-primary ml-auto" type="button" data-toggle="collapse" data-target="#guidelinesCollapse">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="collapse show" id="guidelinesCollapse">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="font-weight-bold text-info mb-3">
                                    <i class="fas fa-percentage mr-2"></i>Komponen Penilaian
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span><strong>Penilaian Project</strong></span>
                                                <span class="badge badge-primary">70%</span>
                                            </div>
                                            <small class="text-muted ml-3">
                                                • Dosen (80%) • Mitra Industri (20%)
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span><strong>Partisipasi Aktivitas</strong></span>
                                                <span class="badge badge-success">30%</span>
                                            </div>
                                            <small class="text-muted ml-3">
                                                • Kehadiran (50%) • Presentasi (50%)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- AP Assessment Details -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="font-weight-bold text-success mb-3">
                                    <i class="fas fa-user-check mr-2"></i>Penilaian Aktivitas Partisipatif (AP) - Detail Komponen
                                </h6>
                                <div class="card border-success mb-4">
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Kehadiran -->
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span><strong>Kehadiran</strong></span>
                                                        <span class="badge badge-success">50% dari AP</span>
                                                    </div>
                                                    <small class="text-muted">
                                                        • Kehadiran perkuliahan<br>
                                                        • Kehadiran bimbingan proyek<br>
                                                        • Kehadiran kegiatan industri<br>
                                                        • Ketepatan waktu
                                                    </small>
                                                </div>
                                            </div>
                                            <!-- Presentasi -->
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span><strong>Presentasi & Partisipasi</strong></span>
                                                        <span class="badge badge-success">50% dari AP</span>
                                                    </div>
                                                    <small class="text-muted">
                                                        • Kualitas presentasi<br>
                                                        • Kemampuan menjawab pertanyaan<br>
                                                        • Partisipasi diskusi<br>
                                                        • Kontribusi tim
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning mt-3">
                                            <strong class="small"><i class="fas fa-info-circle mr-1"></i>Petunjuk Pemberian Nilai AP:</strong><br>
                                            <small>
                                            <strong>1. Kehadiran (50% dari AP):</strong><br>
                                            • Hadir semua sesi: 90-100<br>
                                            • Hadir 80-90% sesi: 80-89<br>
                                            • Hadir 70-79% sesi: 70-79<br>
                                            • Hadir 60-69% sesi: 60-69<br>
                                            • Hadir <60% sesi: <60<br><br>

                                            <strong>2. Presentasi & Partisipasi (50% dari AP):</strong><br>
                                            • Sangat aktif dan presentasi excellent: 90-100<br>
                                            • Aktif dan presentasi baik: 80-89<br>
                                            • Cukup aktif dan presentasi cukup: 70-79<br>
                                            • Kurang aktif: 60-69<br>
                                            • Pasif dan tidak presentasi: <60<br><br>

                                            <strong>Contoh Perhitungan:</strong><br>
                                            Mahasiswa dengan kehadiran 85 dan presentasi 90:<br>
                                            Nilai AP = (85 × 50%) + (90 × 50%) = 87.5<br>
                                            Kontribusi ke Nilai Akhir = 87.5 × 30% = 26.25
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="font-weight-bold text-primary mb-3">
                                    <i class="fas fa-project-diagram mr-2"></i>Penilaian Project - Detail Komponen
                                </h6>

                                <!-- Struktur Hierarki Penilaian -->
                                <div class="alert alert-secondary mb-4">
                                    <strong class="small"><i class="fas fa-sitemap mr-2"></i>Struktur Hierarki Penilaian:</strong><br>
                                    <small>
                                    <strong>Project List</strong> (Kolom Proyek) → <strong>Project Cards</strong> (Item/Kartu Proyek) → <strong>Evaluasi</strong> per Mahasiswa<br>
                                    Setiap list berisi beberapa cards, dan nilai perhitungan dimulai dari tingkat list
                                    </small>
                                </div>

                                <!-- Card untuk Penilaian Project -->
                                <div class="card border-primary mb-4">
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Evaluasi Dosen -->
                                            <div class="col-md-8">
                                                <h6 class="font-weight-bold text-info mb-3">
                                                    <i class="fas fa-chalkboard-teacher mr-2"></i>Evaluasi Dosen per Project List (80% dari Nilai Project)
                                                </h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Kriteria</th>
                                                                <th>Bobot</th>
                                                                <th>Deskripsi Penilaian</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><strong>Kualitas Hasil</strong></td>
                                                                <td><span class="badge badge-primary">30%</span></td>
                                                                <td class="small">
                                                                    • Kesesuaian output dengan tujuan proyek<br>
                                                                    • Kualitas hasil kerja dan deliverables<br>
                                                                    • Pencapaian target yang ditetapkan
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Kompleksitas Teknis</strong></td>
                                                                <td><span class="badge badge-primary">20%</span></td>
                                                                <td class="small">
                                                                    • Tingkat kesulitan teknis yang dihadapi<br>
                                                                    • Pemilihan teknologi dan tools<br>
                                                                    • Solusi teknis yang diterapkan
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Kebutuhan Pengguna</strong></td>
                                                                <td><span class="badge badge-primary">15%</span></td>
                                                                <td class="small">
                                                                    • User experience dan interface<br>
                                                                    • Respons terhadap feedback user<br>
                                                                    • Kesesuaian dengan kebutuhan end-user
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Efisiensi</strong></td>
                                                                <td><span class="badge badge-primary">10%</span></td>
                                                                <td class="small">
                                                                    • Manajemen waktu dan jadwal<br>
                                                                    • Penggunaan sumber daya<br>
                                                                    • Optimasi proses kerja
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Dokumentasi</strong></td>
                                                                <td><span class="badge badge-primary">15%</span></td>
                                                                <td class="small">
                                                                    • Kualitas laporan dan dokumentasi<br>
                                                                    • Keterbacaan dan organisasi dokumen<br>
                                                                    • Kelengkapan catatan proses
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Inisiatif</strong></td>
                                                                <td><span class="badge badge-primary">10%</span></td>
                                                                <td class="small">
                                                                    • Kemandirian dalam problem solving<br>
                                                                    • Kreativitas dan inovasi<br>
                                                                    • Proaktif dalam pengembangan
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Evaluasi Mitra -->
                                            <div class="col-md-4">
                                                <h6 class="font-weight-bold text-success mb-3">
                                                    <i class="fas fa-building mr-2"></i>Evaluasi Mitra Industri per Project List (20% dari Nilai Project)
                                                </h6>
                                                <div class="card border-success">
                                                    <div class="card-body">
                                                        <div class="mb-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span><strong>Komunikasi & Sikap</strong></span>
                                                                <span class="badge badge-success">50%</span>
                                                            </div>
                                                            <small class="text-muted">
                                                                • Etika komunikasi profesional<br>
                                                                • Sikap kerjasama tim<br>
                                                                • Adaptasi di lingkungan kerja<br>
                                                                • Respon terhadap instruksi
                                                            </small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span><strong>Hasil Pekerjaan</strong></span>
                                                                <span class="badge badge-success">50%</span>
                                                            </div>
                                                            <small class="text-muted">
                                                                • Kualitas output kerja<br>
                                                                • Konsistensi performa<br>
                                                                • Kontribusi pada proyek<br>
                                                                • Pemenuhan standar industri
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Contoh Perhitungan -->
                                                <div class="alert alert-info mt-3 mb-0">
                                                    <strong class="small">Contoh Perhitungan:</strong><br>
                                                    <small>
                                                    <strong>Mahasiswa A memiliki 2 Project Lists:</strong><br><br>
                                                    <strong>List 1 (2 Cards):</strong><br>
                                                    • Card 1: Dosen = 85, Mitra = 90<br>
                                                    • Card 2: Dosen = 80, Mitra = 85<br>
                                                    <strong>→ Rata-rata List 1:</strong> Dosen = 82.5, Mitra = 87.5<br><br>
                                                    <strong>List 2 (3 Cards):</strong><br>
                                                    • Card 1: Dosen = 90, Mitra = 95<br>
                                                    • Card 2: Dosen = 75, Mitra = 80<br>
                                                    • Card 3: Dosen = 85, Mitra = 90<br>
                                                    <strong>→ Rata-rata List 2:</strong> Dosen = 83.3, Mitra = 88.3<br><br>
                                                    <strong>Rata-rata Antar List:</strong><br>
                                                    • Dosen = (82.5 + 83.3) ÷ 2 = 82.9<br>
                                                    • Mitra = (87.5 + 88.3) ÷ 2 = 87.9<br><br>
                                                    <strong>Nilai Project:</strong><br>
                                                    (82.9 × 80%) + (87.9 × 20%) = 83.9
                                                    </small>
                                                </div>
                                                    </div>
                                                </div>

                                                <!-- Formula Perhitungan -->
                                                <div class="alert alert-primary mt-3 mb-0">
                                                    <strong class="small">Formula Nilai Project per Mahasiswa:</strong><br>
                                                    <small>
                                                    <strong>Step 1:</strong> Hitung rata-rata per List:<br>
                                                    • Setiap List = ∑(nilai_akhir semua card di list tersebut) ÷ jumlah card per list<br><br>
                                                    <strong>Step 2:</strong> Hitung rata-rata antar List:<br>
                                                    • Nilai Dosen = ∑(rata-rata semua list) ÷ jumlah list<br>
                                                    • Nilai Mitra = ∑(rata-rata semua list) ÷ jumlah list<br><br>
                                                    <strong>Step 3:</strong> Gabungkan nilai:<br>
                                                    (Rata-rata Dosen × 80%) + (Rata-rata Mitra × 20%)<br>
                                                    = <strong>Nilai Project Mahasiswa</strong>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="font-weight-bold text-info mb-3">
                                    <i class="fas fa-handshake mr-2"></i>Kriteria Penilaian Mitra Industri
                                </h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-2"><strong>Komunikasi & Sikap (50%):</strong> Interaksi di lapangan</li>
                                    <li class="mb-2"><strong>Hasil Pekerjaan (50%):</strong> Kualitas deliverables</li>
                                </ul>

                                <h6 class="font-weight-bold text-info mb-3 mt-4">
                                    <i class="fas fa-calendar-check mr-2"></i>Skor Kehadiran
                                </h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-1"><span class="badge badge-success">Hadir</span> = 100</li>
                                    <li class="mb-1"><span class="badge badge-info">Izin</span> = 70</li>
                                    <li class="mb-1"><span class="badge badge-warning">Sakit</span> = 60</li>
                                    <li class="mb-1"><span class="badge badge-secondary">Terlambat</span> = 50</li>
                                    <li class="mb-1"><span class="badge badge-danger">Tanpa Keterangan</span> = 0</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Nilai Final Per Mahasiswa</h6>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="periode_id" class="form-label">Periode</label>
                                <select name="periode_id" id="periode_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Periode --</option>
                                    @foreach($periodes as $periode)
                                        <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                                            {{ $periode->periode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="kelompok_id" class="form-label">Kelompok</label>
                                <select name="kelompok_id" id="kelompok_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Kelompok --</option>
                                    @foreach($kelompoks as $kelompok)
                                        <option value="{{ $kelompok->id }}" {{ $kelompokId == $kelompok->id ? 'selected' : '' }}>
                                            {{ $kelompok->nama_kelompok }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tabel Nilai Final -->
                    @if($mahasiswaNilai->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped nilai-card">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="12%">NIM</th>
                                        <th width="20%">Nama Mahasiswa</th>
                                        <th width="15%">Kelompok</th>
                                        <th width="18%">Nilai Aktifitas Partisipatif</th>
                                        <th width="25%">Nilai Proyek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach($mahasiswaNilai as $mahasiswaId => $data)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $data['mahasiswa']->nim }}</td>
                                            <td>{{ $data['mahasiswa']->nama_mahasiswa }}</td>
                                            <td>{{ $data['kelompok']->nama_kelompok }}</td>
                                            <td>
                                                <!-- Nilai AP -->
                                                <div class="calculation-details">
                                                    @if($data['final_calculation']['nilai_ap']['count'] > 0)
                                                        <div class="mb-2">
                                                            <small class="text-muted">Rata-rata per Aktivitas:</small>
                                                            @foreach($data['final_calculation']['nilai_ap']['presensi_data'] as $presensi)
                                                                <div class="small mb-1 p-2 bg-light rounded">
                                                                    <strong>{{ $presensi->aktivitas_list->name }}</strong>
                                                                    <div class="text-muted">
                                                                        Kehadiran: {{ $presensi->w_ap_kehadiran }} ({{ $presensi->kehadiran_value }}) × 50%,
                                                                        Presentasi: {{ $presensi->w_ap_presentasi }} × 50%,
                                                                        Final AP: {{ number_format($presensi->nilai_final_ap, 2) }}
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="final-calculation p-2 bg-success text-white rounded">
                                                            <small><strong>Nilai Aktifitas Partisipatif:</strong></small><br>
                                                            <small>
                                                                Kehadiran: {{ number_format($data['final_calculation']['nilai_ap']['avg_kehadiran'], 1) }} × 50%<br>
                                                                Presentasi: {{ number_format($data['final_calculation']['nilai_ap']['avg_presentasi'], 1) }} × 50%<br>
                                                                = <strong>{{ number_format($data['final_calculation']['nilai_ap']['average'], 2) }}</strong>
                                                            </small>
                                                        </div>
                                                    @else
                                                        <div class="p-2 bg-secondary text-white rounded">
                                                            <small>Belum ada nilai AP</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                  <!-- Detail Perhitungan per List -->
                                                <div class="calculation-details">
                                                    <div class="mb-2">
                                                        <small class="text-muted">Rata-rata per List Proyek:</small>
                                                        @foreach($data['list_averages'] as $listAvg)
                                                            <div class="small mb-1 p-2 bg-light rounded">
                                                                <strong>{{ $listAvg['list']->name ?: 'Project List #'.$listAvg['list']->id }}</strong>
                                                                <div class="text-muted">
                                                                    Dosen: {{ number_format($listAvg['avg_dosen'], 1) }},
                                                                    Mitra: {{ number_format($listAvg['avg_mitra'], 1) }},
                                                                    Proyek: {{ number_format($listAvg['avg_card'], 1) }}
                                                                    ({{ $listAvg['count'] }} cards)
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="final-calculation p-2 bg-primary text-white rounded">
                                                        <small><strong>Nilai Proyek:</strong></small><br>
                                                        <small>
                                                            Dosen: {{ number_format($data['final_calculation']['avg_dosen'], 1) }} × 80%<br>
                                                            Mitra: {{ number_format($data['final_calculation']['avg_mitra'], 1) }} × 20%<br>
                                                            = <strong>{{ number_format($data['final_calculation']['nilai_project'], 2) }}</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                        
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada data nilai yang tersedia untuk filter yang dipilih.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .info-box-icon {
        border-radius: 0.5rem 0 0 0.5rem;
    }
    .table th {
        background-color: #343a40;
        color: white;
        font-weight: 600;
    }
    .badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    .calculation-details {
        font-size: 0.8rem;
    }
    .calculation-details .bg-light {
        border-left: 3px solid #007bff;
    }
    .final-calculation {
        border-left: 3px solid #0056b3;
    }
    .accordion .card {
        border: none;
        box-shadow: none;
    }
    .accordion .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
</style>
@endpush

@push('scripts')
<script>
function resetFilters() {
    window.location.href = '{{ route("admin.evaluasi.nilai-final") }}';
}

function toggleDetailAP(mahasiswaId) {
    const detailElement = document.getElementById('detail-ap-' + mahasiswaId);
    const iconElement = document.getElementById('icon-ap-' + mahasiswaId);
    const textElement = document.getElementById('text-ap-' + mahasiswaId);

    if (detailElement.classList.contains('d-none')) {
        detailElement.classList.remove('d-none');
        iconElement.classList.remove('fa-chevron-down');
        iconElement.classList.add('fa-chevron-up');
        textElement.textContent = 'Tutup Detail';
    } else {
        detailElement.classList.add('d-none');
        iconElement.classList.remove('fa-chevron-up');
        iconElement.classList.add('fa-chevron-down');
        textElement.textContent = 'Lihat Detail';
    }
}

function toggleDetailProject(mahasiswaId) {
    const detailElement = document.getElementById('detail-project-' + mahasiswaId);
    const iconElement = document.getElementById('icon-project-' + mahasiswaId);
    const textElement = document.getElementById('text-project-' + mahasiswaId);

    if (detailElement.classList.contains('d-none')) {
        detailElement.classList.remove('d-none');
        iconElement.classList.remove('fa-chevron-down');
        iconElement.classList.add('fa-chevron-up');
        textElement.textContent = 'Tutup Detail';
    } else {
        detailElement.classList.add('d-none');
        iconElement.classList.remove('fa-chevron-up');
        iconElement.classList.add('fa-chevron-down');
        textElement.textContent = 'Lihat Detail';
    }
}

// Auto-refresh setiap 30 detik jika ada perubahan data
setInterval(function() {
    if (document.hasFocus()) {
        // Hanya refresh jika user sedang melihat halaman ini
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);

        fetch('{{ route("admin.evaluasi.nilai-final") }}?' + params.toString())
            .then(response => response.text())
            .then(html => {
                // Update hanya jika ada perubahan (sederhana)
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const newTable = newDoc.querySelector('table tbody');
                const currentTable = document.querySelector('table tbody');

                if (newTable && currentTable && newTable.innerHTML !== currentTable.innerHTML) {
                    location.reload();
                }
            })
            .catch(error => console.log('Auto-refresh error:', error));
    }
}, 30000);
</script>
@endpush