<?php

namespace Database\Seeders;

use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShowcaseDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periode = Periode::where('status_periode', 'Aktif')->first() ?? Periode::first();
        if (! $periode) {
            $periode = Periode::create([
                'uuid' => (string) Str::uuid(),
                'periode' => '2025/2026 - Genap',
                'status_periode' => 'Aktif',
            ]);
        }

        $adminUser = User::where('role', 'admin')->first();
        $adminId = $adminUser ? $adminUser->id : 1;

        $matkuls = MataKuliah::all();

        // 1. Update Project 1 if exists
        $p1 = ProjectCard::find(1);
        if ($p1) {
            $p1->update([
                'title' => 'Sistem Monitoring Kualitas Air Tambak Berbasis IoT & Dashboard Real-Time',
                'description' => 'Solusi otomasi pemantauan parameter kualitas air (pH, suhu, salinitas, dan oksigen terlarut) untuk meningkatkan produktivitas tambak udang dan ikan secara terintegrasi melalui cloud dashboard.',
                'jenis_proyek' => 'external',
                'topik_proyek' => 'Internet of Things (IoT) & Smart Aquaculture',
                'skema_pbl' => 'External',
                'nama_mitra' => 'PT Borneo Tambak Lestari',
                'kontak_mitra' => '0812-3456-7890 (Bpk. Bambang Sutrisno)',
                'alamat_mitra' => 'Kawasan Industri Perikanan Jorong, Tanah Laut',
                'status_proyek' => 'Selesai',
                'progress' => 100,
                'link_drive_proyek' => 'https://drive.google.com/drive/folders/sample-pbl-tambak-iot',
                'tanggal_mulai' => now()->subMonths(4),
                'tanggal_selesai' => now()->subDays(10),
            ]);

            if ($matkuls->isNotEmpty()) {
                $p1->mataKuliahs()->sync($matkuls->take(3)->pluck('id'));
            }
        }

        // Demo Projects Data
        $demoProjects = [
            [
                'kelompok' => 'Kelompok 2 - CyberGuard',
                'semester' => 4,
                'title' => 'Aplikasi Manajemen Penjualan & Inventori UMKM Sasirangan Berbasis Mobile',
                'description' => 'Aplikasi mobile cross-platform untuk pelaku industri kreatif kain sasirangan. Dilengkapi fitur POS kasir, pelacakan stok kain per motif, pembukuan keuangan otomatis, dan katalog digital pelanggan.',
                'jenis_proyek' => 'external',
                'topik_proyek' => 'Mobile App Development & Digital Economy',
                'skema_pbl' => 'External',
                'nama_mitra' => 'Rumah Kreatif Sasirangan Tanah Laut',
                'kontak_mitra' => '0821-9876-5432 (Ibu Nurhayati)',
                'alamat_mitra' => 'Jl. Kemakmuran No. 45, Pelaihari, Tanah Laut',
                'labels' => ['Mobile App', 'Flutter', 'UMKM', 'External'],
                'mhs_slice' => [5, 4], // offset 5, length 4
            ],
            [
                'kelompok' => 'Kelompok 3 - AgroTech Innovators',
                'semester' => 4,
                'title' => 'Sistem Deteksi Dini Kebakaran Lahan Gambut Menggunakan Sensor LoRa & GIS',
                'description' => 'Inovasi perangkat telemetri jaringan nirkabel LoRa berdaya rendah untuk memantau titik panas dan kelembapan gambut secara berkala, divisualisasikan dalam peta WebGIS untuk evakuasi cepat.',
                'jenis_proyek' => 'external',
                'topik_proyek' => 'Wireless Sensor Networks & Geografis Information System',
                'skema_pbl' => 'Hibah/Penelitian',
                'nama_mitra' => 'BPBD Kabupaten Tanah Laut',
                'kontak_mitra' => '0813-5566-7788 (Pusdalops PB Tanah Laut)',
                'alamat_mitra' => 'Jl. A. Yani KM. 3.5, Pelaihari',
                'labels' => ['IoT', 'GIS', 'LoRa', 'Hibah/Penelitian'],
                'mhs_slice' => [10, 4],
            ],
            [
                'kelompok' => 'Kelompok 4 - AI Vision Crew',
                'semester' => 6,
                'title' => 'Robot Pemilah Kematangan Buah Sawit Otomatis Berbasis Computer Vision (YOLOv8)',
                'description' => 'Sistem konveyor cerdas dilengkapi kamera berkecepatan tinggi dan model deep learning YOLOv8 untuk mengidentifikasi tingkat kematangan tandan buah segar (TBS) sawit dengan akurasi 96.4%.',
                'jenis_proyek' => 'internal',
                'topik_proyek' => 'Artificial Intelligence & Computer Vision',
                'skema_pbl' => 'Lomba',
                'nama_mitra' => 'Pusat Penelitian Sawit Nasional (Mitra Riset)',
                'kontak_mitra' => '0852-7788-9900 (Dr. Ir. Hendra S.)',
                'alamat_mitra' => 'Laboratorium Rekayasa Robotika Politala',
                'labels' => ['AI', 'Computer Vision', 'YOLOv8', 'Lomba'],
                'mhs_slice' => [15, 4],
            ],
            [
                'kelompok' => 'Kelompok 5 - SmartGov Studio',
                'semester' => 4,
                'title' => 'Portal Layanan Administrasi Desa Digital Terintegrasi QR Code Surat Keterangan',
                'description' => 'Website layanan kependudukan mandiri bagi warga desa untuk pengurusan surat pengantar, perizinan usaha, dan verifikasi keabsahan dokumen via QR-Code dinamis anti-pemalsuan.',
                'jenis_proyek' => 'external',
                'topik_proyek' => 'Web Application & E-Government',
                'skema_pbl' => 'External',
                'nama_mitra' => 'Pemerintah Desa Panggung',
                'kontak_mitra' => '0811-2233-4455 (Sekretariat Desa Panggung)',
                'alamat_mitra' => 'Kantor Kepala Desa Panggung, Kec. Pelaihari',
                'labels' => ['Web', 'E-Gov', 'Laravel', 'External'],
                'mhs_slice' => [20, 4],
            ],
            [
                'kelompok' => 'Kelompok 6 - EduDev Tech',
                'semester' => 2,
                'title' => 'Platform E-Learning Gamifikasi Coding & Literasi Digital Siswa Kejuruan',
                'description' => 'Platform pembelajaran pemrograman interaktif dengan mekanisme perolehan lencana prestasi (badges), papan peringkat (leaderboard), dan tantangan kuis harian untuk menarik minat belajar coding siswa SMK.',
                'jenis_proyek' => 'internal',
                'topik_proyek' => 'Educational Technology & Gamification',
                'skema_pbl' => 'Internal',
                'nama_mitra' => 'SMK Negeri 1 Pelaihari (Sekolah Binaan)',
                'kontak_mitra' => '0878-1122-3344 (Bpk. M. Rian, S.Kom)',
                'alamat_mitra' => 'Jl. Kolonel Soepirman, Pelaihari',
                'labels' => ['Gamifikasi', 'EdTech', 'Vue.js', 'Internal'],
                'mhs_slice' => [25, 4],
            ],
        ];

        $allMhs = Mahasiswa::orderBy('id')->get();

        foreach ($demoProjects as $item) {
            $kelompok = Kelompok::firstOrCreate(
                [
                    'nama_kelompok' => $item['kelompok'],
                    'periode_id' => $periode->id,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'skema_kategori' => $item['skema_pbl'],
                    'semester' => $item['semester'],
                    'max_members' => 4,
                    'status_bergabung' => 'penuh',
                    'link_drive' => 'https://drive.google.com/drive/folders/showcase-'.Str::slug($item['kelompok']),
                ]
            );

            // Assign anggota mahasiswa
            [$offset, $length] = $item['mhs_slice'];
            $selectedMhs = $allMhs->slice($offset, $length);

            foreach ($selectedMhs as $idx => $mhs) {
                $role = ($idx === 0) ? 'Ketua' : 'Anggota';
                // Hindari duplikasi pivot
                if (! $kelompok->mahasiswas()->where('mahasiswa_id', $mhs->id)->exists()) {
                    $kelompok->mahasiswas()->attach($mhs->id, [
                        'periode_id' => $periode->id,
                        'role' => $role,
                    ]);
                }
            }

            // Create Project Card
            $card = ProjectCard::firstOrCreate(
                [
                    'kelompok_id' => $kelompok->id,
                    'title' => $item['title'],
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'periode_id' => $periode->id,
                    'description' => $item['description'],
                    'jenis_proyek' => $item['jenis_proyek'],
                    'topik_proyek' => $item['topik_proyek'],
                    'skema_pbl' => $item['skema_pbl'],
                    'nama_mitra' => $item['nama_mitra'],
                    'kontak_mitra' => $item['kontak_mitra'],
                    'alamat_mitra' => $item['alamat_mitra'],
                    'status_proyek' => 'Selesai',
                    'progress' => 100,
                    'link_drive_proyek' => 'https://drive.google.com/drive/folders/showcase-'.Str::slug($item['title']),
                    'tanggal_mulai' => now()->subMonths(3),
                    'tanggal_selesai' => now()->subDays(5),
                    'proposal_file_path' => $item['skema_pbl'] === 'Internal' ? 'proposals/demo_proposal.pdf' : null,
                    'status_proposal' => $item['skema_pbl'] === 'Internal' ? 'disetujui' : 'pending',
                    'labels' => $item['labels'],
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            // Hubungkan beberapa mata kuliah jika ada
            if ($matkuls->count() >= 3) {
                $randomMatkuls = $matkuls->random(min(3, $matkuls->count()))->pluck('id');
                $card->mataKuliahs()->sync($randomMatkuls);
            }
        }
    }
}
