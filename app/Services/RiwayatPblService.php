<?php

namespace App\Services;

use App\Models\AktivitasCard;
use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMitra;
use App\Models\EvaluasiNilaiAP;
use App\Models\KunjunganMitra;
use App\Models\Mahasiswa;
use App\Models\ProjectCard;

class RiwayatPblService
{
    public function __construct(
        protected GradingService $gradingService
    ) {}

    /**
     * Dapatkan riwayat komprehensif PBL untuk seorang mahasiswa.
     *
     * @return array<string, mixed>
     */
    public function getRiwayatMahasiswa(Mahasiswa $mahasiswa): array
    {
        $mahasiswa->loadMissing(['user', 'kelas', 'kelompoks.periode']);

        $riwayatProyek = collect();
        $totalAktivitas = 0;
        $totalNilaiAkhir = 0;
        $countNilaiAkhir = 0;

        foreach ($mahasiswa->kelompoks as $kelompok) {
            $periode = $kelompok->periode;
            $peran = ucfirst(strtolower($kelompok->pivot->role ?? 'Anggota'));

            // Ambil ProjectCard(s) kelompok
            $projectCards = ProjectCard::with(['mataKuliahs.dosenPengampu', 'createdBy'])
                ->where('kelompok_id', $kelompok->id)
                ->orderBy('created_at', 'asc')
                ->get();

            // Jika belum ada kartu proyek, buatkan representasi default berbasis kelompok
            if ($projectCards->isEmpty()) {
                $projectCards = collect([
                    new ProjectCard([
                        'title' => 'Proyek PBL - '.$kelompok->nama_kelompok,
                        'description' => 'Proyek PBL terdaftar pada kelompok '.$kelompok->nama_kelompok,
                        'nama_mitra' => $kelompok->skema_kategori === 'External' ? 'Mitra Industri / Eksternal' : 'Internal Program Studi TRKJ Politeknik Negeri Tanah Laut',
                        'alamat_mitra' => $kelompok->skema_kategori === 'External' ? 'Wilayah Kalimantan Selatan' : 'Jl. A. Yani Km. 06 Desa Panggung, Pelaihari, Kab. Tanah Laut',
                        'skema_pbl' => $kelompok->skema_kategori ?? 'Internal',
                        'progress' => 0,
                        'status_proyek' => 'Dalam Pengerjaan',
                    ]),
                ]);
            }

            // Kunjungan Mitra
            $kunjungan = KunjunganMitra::where('kelompok_id', $kelompok->id)->get();

            // Evaluasi Dosen untuk mahasiswa ini pada kelompok ini
            $evalDosen = EvaluasiDosen::with('evaluator')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('kelompok_id', $kelompok->id)
                ->latest()
                ->first();

            // Evaluasi Mitra untuk mahasiswa ini
            $evalMitra = EvaluasiMitra::with('evaluator')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('kelompok_id', $kelompok->id)
                ->latest()
                ->first();

            // Nilai Aktivitas & Presensi (AP)
            $nilaiAPList = EvaluasiNilaiAP::with('aktivitasList')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('kelompok_id', $kelompok->id)
                ->get();

            // Logbook & Aktivitas yang dikerjakan
            $aktivitas = AktivitasCard::with(['listAktivitas', 'createdBy'])
                ->where('kelompok_id', $kelompok->id)
                ->orderBy('tanggal_aktivitas', 'desc')
                ->get();

            $totalAktivitas += $aktivitas->count();

            // Hitung nilai dan 4 Pilar
            $relevansi = $evalDosen?->d_relevansi ?? null;
            $kualitas = $evalDosen?->d_kualitas ?? $evalDosen?->d_hasil ?? null;
            $kreativitas = $evalDosen?->d_kreativitas ?? null;
            $estetika = $evalDosen?->d_estetika ?? null;

            $avgDosen = $evalDosen?->nilai_akhir ?? $evalDosen?->rata_rata ?? 0;
            $avgMitra = $evalMitra?->nilai_akhir ?? 0;

            // Hitung Nilai AP (Kehadiran & Presentasi)
            $avgAP = 0;
            if ($nilaiAPList->isNotEmpty()) {
                $totalAPScore = 0;
                foreach ($nilaiAPList as $ap) {
                    $valKehadiran = $this->gradingService->convertAttendanceToValue($ap->w_ap_kehadiran);
                    $valPresentasi = (float) ($ap->w_ap_presentasi ?? 0);
                    $totalAPScore += $this->gradingService->calculateActivityScore($valKehadiran, $valPresentasi);
                }
                $avgAP = $totalAPScore / $nilaiAPList->count();
            }

            // Hitung Nilai Gabungan
            $nilaiProyek = $this->gradingService->calculateProjectScore((float) $avgDosen, (float) $avgMitra);
            $nilaiAkhir = $this->gradingService->calculateFinalScore($nilaiProyek, $avgAP);
            $grade = $this->gradingService->calculateRubricGrade($nilaiAkhir > 0 ? $nilaiAkhir : null);

            if ($nilaiAkhir > 0) {
                $totalNilaiAkhir += $nilaiAkhir;
                $countNilaiAkhir++;
            }

            foreach ($projectCards as $card) {
                // Dimana PBL dilaksanakan
                $namaTempat = $card->nama_mitra
                    ?: ($kunjungan->first()?->perusahaan
                    ?: ($kelompok->skema_kategori === 'External' ? 'Mitra Industri / Eksternal' : 'Internal Program Studi TRKJ Politeknik Negeri Tanah Laut'));

                $alamatTempat = $card->alamat_mitra
                    ?: ($kunjungan->first()?->alamat
                    ?: 'Laboratorium Sistem Informasi & Jaringan Komputer, Politeknik Negeri Tanah Laut');

                $kontakMitra = $card->kontak_mitra ?: '-';
                $skema = $card->skema_pbl ?: ($kelompok->skema_kategori ?? 'Internal');

                // Generate Kesimpulan untuk Proyek Ini
                $kesimpulan = $this->generateProjectConclusion(
                    mahasiswa: $mahasiswa,
                    peran: $peran,
                    card: $card,
                    namaTempat: $namaTempat,
                    skema: $skema,
                    progress: (int) ($card->progress ?? 0),
                    relevansi: $relevansi,
                    kualitas: $kualitas,
                    kreativitas: $kreativitas,
                    estetika: $estetika,
                    nilaiAkhir: $nilaiAkhir,
                    grade: $grade,
                    aktivitasCount: $aktivitas->count(),
                    evalDosen: $evalDosen
                );

                $riwayatProyek->push([
                    'kelompok' => $kelompok,
                    'periode' => $periode,
                    'peran' => $peran,
                    'card' => $card,
                    // PBL Dimana
                    'tempat' => [
                        'nama' => $namaTempat,
                        'alamat' => $alamatTempat,
                        'kontak' => $kontakMitra,
                        'skema' => $skema,
                        'kunjungan_count' => $kunjungan->count(),
                        'kunjungan_list' => $kunjungan,
                    ],
                    // Apa yang Dikerjakan
                    'pekerjaan' => [
                        'judul' => $card->title,
                        'deskripsi' => $card->description,
                        'jenis' => $card->jenis_proyek ?? $skema,
                        'topik' => $card->topik_proyek,
                        'progress' => (int) ($card->progress ?? 0),
                        'status' => $card->status_proyek ?? ($card->progress >= 100 ? 'Selesai' : 'Dalam Pengerjaan'),
                        'link_drive' => $card->link_drive_proyek ?: $kelompok->link_drive,
                        'proposal_url' => $card->proposal_url,
                        'mata_kuliah' => $card->mataKuliahs,
                        'aktivitas' => $aktivitas,
                        'total_aktivitas' => $aktivitas->count(),
                    ],
                    // 4 Pilar & Penilaian
                    'penilaian' => [
                        'relevansi' => $relevansi,
                        'kualitas' => $kualitas,
                        'kreativitas' => $kreativitas,
                        'estetika' => $estetika,
                        'nilai_dosen' => (float) $avgDosen,
                        'nilai_mitra' => (float) $avgMitra,
                        'nilai_ap' => (float) $avgAP,
                        'nilai_akhir' => (float) $nilaiAkhir,
                        'grade' => $grade,
                        'catatan_dosen' => $evalDosen?->catatan,
                        'evaluator_nama' => $evalDosen?->evaluator?->nama_user,
                    ],
                    // Kesimpulan
                    'kesimpulan' => $kesimpulan,
                ]);
            }
        }

        // Rata-rata keseluruhan
        $avgAllNilai = $countNilaiAkhir > 0 ? ($totalNilaiAkhir / $countNilaiAkhir) : null;
        $overallGrade = $this->gradingService->calculateRubricGrade($avgAllNilai);

        $overallConclusion = $this->generateOverallConclusion(
            mahasiswa: $mahasiswa,
            totalProyek: $riwayatProyek->count(),
            totalAktivitas: $totalAktivitas,
            avgNilai: $avgAllNilai,
            grade: $overallGrade
        );

        return [
            'mahasiswa' => $mahasiswa,
            'riwayat' => $riwayatProyek,
            'summary' => [
                'total_kelompok' => $mahasiswa->kelompoks->count(),
                'total_proyek' => $riwayatProyek->count(),
                'total_aktivitas' => $totalAktivitas,
                'avg_nilai_akhir' => $avgAllNilai,
                'overall_grade' => $overallGrade,
                'overall_kesimpulan' => $overallConclusion,
            ],
        ];
    }

    /**
     * Membuat narasi kesimpulan komprehensif per proyek PBL.
     *
     * @return array<string, string>
     */
    protected function generateProjectConclusion(
        Mahasiswa $mahasiswa,
        string $peran,
        ProjectCard $card,
        string $namaTempat,
        string $skema,
        int $progress,
        ?int $relevansi,
        ?int $kualitas,
        ?int $kreativitas,
        ?int $estetika,
        float $nilaiAkhir,
        ?string $grade,
        int $aktivitasCount,
        ?EvaluasiDosen $evalDosen
    ): array {
        // 1. Status Penyelesaian
        $statusPenyelesaian = match (true) {
            $progress >= 100 && $nilaiAkhir >= 80 => 'Selesai Sempurna dengan Predikat Sangat Memuaskan',
            $progress >= 100 => 'Selesai Memenuhi Standar Luaran Proyek',
            $progress >= 70 => 'Dalam Tahap Finalisasi Produk & Pengujian',
            $progress >= 40 => 'Dalam Tahap Pengembangan & Implementasi Sistem',
            default => 'Tahap Perencanaan & Penyusunan Desain Solusi',
        };

        // 2. Analisis 4 Pilar Keunggulan
        $pilars = [
            'Relevansi Konsep' => $relevansi ?? 0,
            'Kualitas Produk' => $kualitas ?? 0,
            'Kreativitas' => $kreativitas ?? 0,
            'Estetika' => $estetika ?? 0,
        ];
        arsort($pilars);
        $topPillar = array_key_first($pilars);
        $topScore = current($pilars);

        $analisisPilar = '';
        if ($topScore > 0) {
            $analisisPilar = "Keunggulan utama mahasiswa terletak pada pilar {$topPillar} (skor {$topScore}/100) ";
            if ($topPillar === 'Kualitas Produk') {
                $analisisPilar .= 'yang mencerminkan fungsionalitas dan keandalan sistem yang dibangun bekerja dengan sangat baik.';
            } elseif ($topPillar === 'Relevansi Konsep') {
                $analisisPilar .= 'yang membuktikan pemahaman teori mata kuliah terintegrasi selaras dengan penyelesaian masalah mitra.';
            } elseif ($topPillar === 'Kreativitas') {
                $analisisPilar .= 'yang mencerminkan ide orisinalitas tinggi dan inovasi dalam merancang arsitektur solusi.';
            } else {
                $analisisPilar .= 'yang menunjukkan kualitas antarmuka UI/UX, kerapian tata kelola sistem, dan penyajian produk yang profesional.';
            }
        } else {
            $analisisPilar = 'Penilaian 4 pilar (Relevansi Konsep, Kualitas Produk, Kreativitas, Estetika) sedang dalam proses perumusan oleh Dosen Penilai.';
        }

        // 3. Kontribusi & Rekam Jejak
        $kontribusi = "Berperan sebagai {$peran} dalam kelompok, dengan kontribusi aktif pada {$aktivitasCount} catatan kegiatan/logbook terlapor.";
        if (strtolower($peran) === 'ketua') {
            $kontribusi .= ' Mahasiswa menunjukkan kapasitas kepemimpinan tim, pembagian tugas teknis, serta komunikasi yang efektif dengan dosen pembimbing dan mitra.';
        } else {
            $kontribusi .= ' Mahasiswa menunjukkan kerja sama tim yang solid, kedisiplinan jadwal pengerjaan, dan eksekusi tugas teknis secara konsisten.';
        }

        // 4. Sintesis Portofolio SKPI (Surat Keterangan Pendamping Ijazah)
        $namaMhs = $mahasiswa->nama_mahasiswa;
        $judul = $card->title ?: 'Proyek PBL';
        $gradeText = $grade ? " (Grade {$grade})" : '';

        $portofolio = "Mahasiswa {$namaMhs} telah melaksanakan implementasi Project Based Learning (PBL) pada skema {$skema} bertempat di {$namaTempat} dengan mengangkat karya berjudul \"{$judul}\". Proyek berhasil mencapai progres {$progress}%{$gradeText}. Karya ini merefleksikan penguasaan kompetensi praktis teknik rekayasa komputer dan jaringan yang siap diterapkan pada dunia industri.";

        return [
            'status' => $statusPenyelesaian,
            'analisis_pilar' => $analisisPilar,
            'kontribusi' => $kontribusi,
            'narasi_portofolio' => $portofolio,
        ];
    }

    /**
     * Membuat kesimpulan agregat untuk seluruh masa studi PBL mahasiswa.
     */
    protected function generateOverallConclusion(
        Mahasiswa $mahasiswa,
        int $totalProyek,
        int $totalAktivitas,
        ?float $avgNilai,
        ?string $grade
    ): string {
        $nama = $mahasiswa->nama_mahasiswa;
        $nim = $mahasiswa->nim;

        if ($totalProyek === 0) {
            return "Mahasiswa {$nama} (NIM: {$nim}) belum tercatat memiliki riwayat keikutsertaan kelompok atau proyek PBL aktif pada sistem.";
        }

        $gradeInfo = $grade ? "memperoleh rata-rata capaian mutu Grade {$grade} (Nilai: ".number_format((float) $avgNilai, 2).')' : 'sedang dalam proses rekapitulasi penilaian akhir';

        return "Mahasiswa {$nama} (NIM: {$nim}) telah berpartisipasi dalam {$totalProyek} proyek PBL dengan total {$totalAktivitas} catatan aktivitas dan logbook tervalidasi. Secara keseluruhan, mahasiswa {$gradeInfo}. Portofolio ini membuktikan kemampuan problem-solving, kerja sama tim, dan keahlian terapan di bidang Teknologi Rekayasa Komputer Jaringan (TRKJ) Politeknik Negeri Tanah Laut.";
    }
}
