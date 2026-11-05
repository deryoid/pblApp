{{-- Dossier Riwayat PBL Per Orang --}}
@php
    $mhsUser = $mahasiswa->user;
    $photoUrl = ($mhsUser && $mhsUser->profile_photo_data_url) ? $mhsUser->profile_photo_data_url : asset('sbadmin2/img/avatar-default.svg');
@endphp

<style>
    .riwayat-header-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .riwayat-avatar {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .badge-stat {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 0.6rem 1rem;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 110px;
    }
    .badge-stat .val {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .badge-stat .lbl {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.85;
    }
    .card-project-history {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 2rem;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .card-project-history:hover {
        box-shadow: 0 12px 28px rgba(0,0,0,0.07);
    }
    .project-header-strip {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        border-top-left-radius: 14px;
        border-top-right-radius: 14px;
    }
    .pillar-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        transition: all 0.2s;
    }
    .pillar-card:hover {
        background: #ffffff;
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
    .pillar-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    .pillar-score {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
    }
    .pillar-weight {
        font-size: 0.7rem;
        font-weight: 600;
        color: #2563eb;
        background: #eff6ff;
        padding: 2px 8px;
        border-radius: 9999px;
        display: inline-block;
        margin-top: 4px;
    }
    .conclusion-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
    }
</style>

{{-- Profil Singkat & Kartu Metrik Mahasiswa --}}
<div class="riwayat-header-card p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-lg-7 d-flex align-items-center flex-column flex-sm-row text-center text-sm-left mb-3 mb-lg-0">
            <img src="{{ $photoUrl }}" alt="Foto {{ $mahasiswa->nama_mahasiswa }}" width="100" height="100" class="riwayat-avatar mr-sm-4 mb-3 mb-sm-0">
            <div>
                <div class="d-flex align-items-center justify-content-center justify-content-sm-start flex-wrap gap-2 mb-1">
                    <h3 class="font-weight-bold mb-0 text-white">{{ $mahasiswa->nama_mahasiswa }}</h3>
                    <span class="badge badge-primary px-2 py-1 ml-sm-2" style="font-size:0.8rem;">
                        <i class="fas fa-id-badge mr-1"></i> NIM: {{ $mahasiswa->nim }}
                    </span>
                </div>
                <div class="text-white-50 small mb-2">
                    <span class="mr-3"><i class="fas fa-door-open mr-1"></i> Kelas: <strong>{{ $mahasiswa->kelas->kelas ?? '-' }}</strong></span>
                    <span class="mr-3"><i class="fas fa-calendar-alt mr-1"></i> Semester: <strong>{{ $mahasiswa->semester ?? ($mahasiswa->kelas->semester ?? '-') }}</strong></span>
                    <span><i class="fas fa-envelope mr-1"></i> {{ $mhsUser->email ?? 'Belum ada email' }}</span>
                </div>
                <div>
                    @if($mhsUser)
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Akun Terhubung</span>
                    @else
                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> Belum Registrasi Akun</span>
                    @endif
                    @if($mahasiswa->is_profile_complete)
                        <span class="badge badge-info px-2 py-1 ml-1"><i class="fas fa-user-check mr-1"></i> Profil Lengkap</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="d-flex justify-content-center justify-content-lg-end flex-wrap gap-2">
                <div class="badge-stat m-1">
                    <div class="val text-warning">{{ $summary['total_proyek'] }}</div>
                    <div class="lbl">Proyek PBL</div>
                </div>
                <div class="badge-stat m-1">
                    <div class="val text-info">{{ $summary['total_aktivitas'] }}</div>
                    <div class="lbl">Logbook Selesai</div>
                </div>
                <div class="badge-stat m-1">
                    <div class="val text-success">
                        {{ $summary['avg_nilai_akhir'] ? number_format($summary['avg_nilai_akhir'], 1) : '-' }}
                    </div>
                    <div class="lbl">Rata-Rata Nilai</div>
                </div>
                <div class="badge-stat m-1">
                    <div class="val text-primary">
                        {{ $summary['overall_grade'] ?? '-' }}
                    </div>
                    <div class="lbl">Grade Capaian</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Kotak Kesimpulan Eksekutif Keseluruhan --}}
<div class="card shadow-sm border-0 rounded-xl mb-4" style="border-left: 5px solid #2563eb !important;">
    <div class="card-body p-4">
        <div class="d-flex align-items-start">
            <div class="mr-3 mt-1 text-primary">
                <i class="fas fa-award fa-2x"></i>
            </div>
            <div>
                <h5 class="font-weight-bold text-gray-900 mb-1">Kesimpulan Portofolio Rekayasa PBL TRKJ Politala</h5>
                <p class="text-gray-700 mb-0" style="line-height: 1.6;">
                    {{ $summary['overall_kesimpulan'] }}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Header Riwayat Proyek PBL --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="font-weight-bold text-gray-900 mb-0">
        <i class="fas fa-layer-group text-primary mr-2"></i>Daftar Proyek & Partisipasi PBL ({{ $riwayat->count() }})
    </h5>
    <span class="text-muted small">
        Sistem Penilaian 4 Pilar: Relevansi (25%), Kualitas (25%), Kreativitas (25%), Estetika (25%)
    </span>
</div>

@forelse($riwayat as $index => $item)
    @php
        $card = $item['card'];
        $kelompok = $item['kelompok'];
        $tempat = $item['tempat'];
        $pekerjaan = $item['pekerjaan'];
        $penilaian = $item['penilaian'];
        $kesimpulan = $item['kesimpulan'];
    @endphp

    <div class="card-project-history">
        {{-- Strip Atas: Judul Proyek, Periode, dan Status --}}
        <div class="project-header-strip d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <span class="badge badge-dark px-2 py-1 mr-2 font-weight-bold">#{{ $index + 1 }}</span>
                    <h5 class="font-weight-bold text-gray-900 mb-0">{{ $pekerjaan['judul'] }}</h5>
                    <span class="badge {{ $item['peran'] === 'Ketua' ? 'badge-primary' : 'badge-secondary' }} ml-2">
                        <i class="fas {{ $item['peran'] === 'Ketua' ? 'fa-crown' : 'fa-user' }} mr-1"></i> Peran: {{ $item['peran'] }}
                    </span>
                    <span class="badge badge-info ml-1">
                        <i class="fas fa-users mr-1"></i> {{ $kelompok->nama_kelompok }}
                    </span>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-calendar mr-1"></i> Periode: <strong>{{ $item['periode']->periode ?? '-' }}</strong>
                    @if($kelompok->semester)
                        <span class="mx-2">•</span> Semester {{ $kelompok->semester }}
                    @endif
                    <span class="mx-2">•</span> Skema: <strong>{{ $tempat['skema'] }}</strong>
                </div>
            </div>

            <div class="mt-3 mt-md-0 text-md-right">
                <div class="d-inline-flex align-items-center">
                    <div class="mr-3 text-right">
                        <div class="small font-weight-bold text-gray-700">Progres Proyek</div>
                        <div class="h6 font-weight-bold mb-0 {{ $pekerjaan['progress'] >= 100 ? 'text-success' : 'text-primary' }}">
                            {{ $pekerjaan['progress'] }}%
                        </div>
                    </div>
                    <span class="badge {{ $pekerjaan['progress'] >= 100 ? 'badge-success' : 'badge-warning text-dark' }} px-3 py-2 font-weight-bold" style="font-size:0.85rem;">
                        <i class="fas {{ $pekerjaan['progress'] >= 100 ? 'fa-check-circle' : 'fa-spinner fa-spin' }} mr-1"></i>
                        {{ $pekerjaan['status'] }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row">
                
                {{-- KETERANGAN 1: DIA PBL DIMANA --}}
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-0 shadow-none bg-light rounded-xl p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width:32px;height:32px;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h6 class="font-weight-bold text-gray-900 mb-0">1. Keterangan Tempat PBL (PBL Dimana)</h6>
                        </div>

                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted font-weight-bold pl-0" style="width: 140px;">Nama Mitra/Instansi</td>
                                    <td class="font-weight-bold text-gray-900">: {{ $tempat['nama'] }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted font-weight-bold pl-0">Skema PBL</td>
                                    <td>: <span class="badge badge-outline-secondary font-weight-bold">{{ $tempat['skema'] }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted font-weight-bold pl-0">Lokasi / Alamat</td>
                                    <td>: {{ $tempat['alamat'] }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted font-weight-bold pl-0">Kontak / Narahubung</td>
                                    <td>: {{ $tempat['kontak'] }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted font-weight-bold pl-0">Kunjungan Mitra</td>
                                    <td>: 
                                        @if($tempat['kunjungan_count'] > 0)
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i> {{ $tempat['kunjungan_count'] }} Kunjungan Terverifikasi</span>
                                        @else
                                            <span class="text-muted small">Implementasi internal kampus / Tidak memerlukan kunjungan lapangan</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- KETERANGAN 2: APA YANG DIA KERJAKAN --}}
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-0 shadow-none bg-light rounded-xl p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width:32px;height:32px;">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h6 class="font-weight-bold text-gray-900 mb-0">2. Keterangan Pekerjaan Proyek (Apa yang Dikerjakan)</h6>
                        </div>

                        <div class="mb-2">
                            <span class="text-muted font-weight-bold small text-uppercase">Deskripsi & Ruang Lingkup:</span>
                            <p class="text-gray-800 small mb-2" style="line-height:1.5;">
                                {{ $pekerjaan['deskripsi'] ?: 'Pengerjaan sistem terintegrasi sesuai kebutuhan modul PBL TRKJ.' }}
                            </p>
                        </div>

                        @if(!empty($pekerjaan['mata_kuliah']) && count($pekerjaan['mata_kuliah']) > 0)
                            <div class="mb-2">
                                <span class="text-muted font-weight-bold small text-uppercase">Mata Kuliah Terintegrasi:</span>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($pekerjaan['mata_kuliah'] as $mk)
                                        <span class="badge badge-white border text-primary small py-1 px-2 font-weight-semibold">
                                            <i class="fas fa-book mr-1"></i> {{ $mk->nama_mata_kuliah ?? $mk->nama }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                            <span class="text-muted small font-weight-bold align-self-center mr-1">Aktivitas Terlapor:</span>
                            <span class="badge badge-primary font-weight-bold"><i class="fas fa-clipboard-list mr-1"></i> {{ $pekerjaan['total_aktivitas'] }} Logbook</span>

                            @if($pekerjaan['proposal_url'])
                                <a href="{{ $pekerjaan['proposal_url'] }}" target="_blank" class="btn btn-xs btn-outline-danger py-1 px-2 font-weight-bold">
                                    <i class="fas fa-file-pdf mr-1"></i> Berkas Proposal
                                </a>
                            @endif

                            @if($pekerjaan['link_drive'])
                                <a href="{{ $pekerjaan['link_drive'] }}" target="_blank" class="btn btn-xs btn-outline-primary py-1 px-2 font-weight-bold">
                                    <i class="fab fa-google-drive mr-1"></i> Folder Luaran Drive
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- LOGBOOK & AKTIVITAS TEKNIS YANG DIKERJAKAN --}}
            @if(!empty($pekerjaan['aktivitas']) && count($pekerjaan['aktivitas']) > 0)
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="font-weight-bold text-gray-800 small text-uppercase">
                            <i class="fas fa-calendar-check text-info mr-1"></i> Rincian Aktivitas & Logbook Pekerjaan Terlapor
                        </span>
                        <span class="badge badge-secondary small">{{ count($pekerjaan['aktivitas']) }} kegiatan</span>
                    </div>

                    <div class="table-responsive bg-white border rounded">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="width: 140px;">Tanggal</th>
                                    <th style="width: 150px;">Tahapan / Modul</th>
                                    <th>Uraian Pekerjaan Teknis Mahasiswa</th>
                                    <th style="width: 120px;" class="text-center">Pelapor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pekerjaan['aktivitas']->take(6) as $aIdx => $akt)
                                    <tr>
                                        <td class="text-center align-middle text-muted">{{ $aIdx + 1 }}</td>
                                        <td class="align-middle text-nowrap">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ $akt->tanggal_aktivitas ? $akt->tanggal_aktivitas->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="align-middle font-weight-bold text-primary">
                                            {{ $akt->listAktivitas->name ?? 'Logbook Reguler' }}
                                        </td>
                                        <td class="align-middle text-gray-800">
                                            {{ $akt->description ?? '-' }}
                                        </td>
                                        <td class="align-middle text-center small text-muted">
                                            {{ $akt->createdBy->nama_user ?? 'Tim' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($pekerjaan['aktivitas']) > 6)
                        <div class="text-center mt-1">
                            <span class="text-muted small">Menampilkan 6 dari total {{ count($pekerjaan['aktivitas']) }} kegiatan logbook.</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 3. EVALUASI 4 PILAR (25% MASING-MASING) --}}
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="font-weight-bold text-gray-900 mb-0">
                        <i class="fas fa-balance-scale text-primary mr-1"></i> 3. Evaluasi Produk Berdasarkan 4 Pilar (Bobot Masing-masing 25%)
                    </h6>
                    @if($penilaian['nilai_akhir'] > 0)
                        <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size:0.85rem;">
                            Nilai Akhir: {{ number_format($penilaian['nilai_akhir'], 2) }} (Grade {{ $penilaian['grade'] }})
                        </span>
                    @endif
                </div>

                <div class="row">
                    {{-- Pilar 1: Relevansi Konsep --}}
                    <div class="col-6 col-md-3 mb-3">
                        <div class="pillar-card h-100">
                            <div class="pillar-title">Relevansi Konsep</div>
                            <div class="pillar-score text-primary">
                                {{ $penilaian['relevansi'] ?? '-' }}
                            </div>
                            <div class="pillar-weight">Bobot 25%</div>
                            <p class="small text-muted mt-2 mb-0" style="font-size:0.75rem;">
                                Kesesuaian capaian teori mata kuliah terintegrasi dengan kebutuhan industri.
                            </p>
                        </div>
                    </div>

                    {{-- Pilar 2: Kualitas Produk --}}
                    <div class="col-6 col-md-3 mb-3">
                        <div class="pillar-card h-100">
                            <div class="pillar-title">Kualitas Produk</div>
                            <div class="pillar-score text-success">
                                {{ $penilaian['kualitas'] ?? '-' }}
                            </div>
                            <div class="pillar-weight">Bobot 25%</div>
                            <p class="small text-muted mt-2 mb-0" style="font-size:0.75rem;">
                                Fungsionalitas, keandalan sistem, kestabilan uji coba, dan ketuntasan fitur.
                            </p>
                        </div>
                    </div>

                    {{-- Pilar 3: Kreativitas --}}
                    <div class="col-6 col-md-3 mb-3">
                        <div class="pillar-card h-100">
                            <div class="pillar-title">Kreativitas</div>
                            <div class="pillar-score text-warning">
                                {{ $penilaian['kreativitas'] ?? '-' }}
                            </div>
                            <div class="pillar-weight">Bobot 25%</div>
                            <p class="small text-muted mt-2 mb-0" style="font-size:0.75rem;">
                                Inovasi gagasan, orisinalitas rancangan, dan pendekatan efisien dalam solusi.
                            </p>
                        </div>
                    </div>

                    {{-- Pilar 4: Estetika --}}
                    <div class="col-6 col-md-3 mb-3">
                        <div class="pillar-card h-100">
                            <div class="pillar-title">Estetika</div>
                            <div class="pillar-score text-info">
                                {{ $penilaian['estetika'] ?? '-' }}
                            </div>
                            <div class="pillar-weight">Bobot 25%</div>
                            <p class="small text-muted mt-2 mb-0" style="font-size:0.75rem;">
                                Desain antarmuka (UI/UX), kerapian tata letak, dokumentasi, dan standar penyajian.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Catatan Dosen Evaluator --}}
                @if(!empty($penilaian['catatan_dosen']))
                    <div class="alert alert-light border small text-gray-800 mt-2 mb-0">
                        <i class="fas fa-comment-dots text-primary mr-1"></i>
                        <strong>Catatan Umpan Balik Dosen Evaluator ({{ $penilaian['evaluator_nama'] ?? 'Tim Penilai' }}):</strong>
                        "{{ $penilaian['catatan_dosen'] }}"
                    </div>
                @endif
            </div>

            {{-- 4. KESIMPULAN PROYEK & PORTOFOLIO --}}
            <div class="conclusion-box">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-clipboard-check text-success fa-lg mr-2"></i>
                    <h6 class="font-weight-bold text-success mb-0">Kesimpulan Riwayat Proyek & Portofolio Mahasiswa</h6>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="small text-gray-700 mb-1">
                            <strong>Status Pencapaian:</strong> <span class="text-gray-900 font-weight-bold">{{ $kesimpulan['status'] }}</span>
                        </div>
                        <div class="small text-gray-700 mb-1">
                            <strong>Analisis Kompetensi 4 Pilar:</strong> {{ $kesimpulan['analisis_pilar'] }}
                        </div>
                        <div class="small text-gray-700">
                            <strong>Kontribusi Personal:</strong> {{ $kesimpulan['kontribusi'] }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-2 bg-white rounded border border-success small text-gray-800">
                            <strong class="text-success"><i class="fas fa-file-signature mr-1"></i> Pernyataan Portofolio SKPI:</strong>
                            <p class="mb-0 mt-1" style="font-size:0.8rem; line-height:1.5;">
                                "{{ $kesimpulan['narasi_portofolio'] }}"
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@empty
    <div class="card shadow-sm border-0 rounded-xl p-5 text-center">
        <i class="fas fa-folder-open text-muted fa-4x mb-3"></i>
        <h5 class="font-weight-bold text-gray-800">Belum Ada Riwayat Partisipasi PBL</h5>
        <p class="text-muted small max-w-md mx-auto mb-3">
            Mahasiswa ini belum tercatat tergabung ke dalam kelompok PBL atau belum memiliki kartu proyek yang aktif.
        </p>
    </div>
@endforelse
