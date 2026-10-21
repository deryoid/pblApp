<?php

namespace Database\Seeders;

use App\Models\MataKuliah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $matkulList = [
            // Semester 1
            ['kode_matkul' => 'MK101', 'nama_matkul' => 'Algoritma dan Pemrograman', 'semester' => 1, 'sks' => 3, 'deskripsi' => 'Konsep dasar logika pemrograman dan algoritma komputer'],
            ['kode_matkul' => 'MK102', 'nama_matkul' => 'Pengantar Teknologi Informasi', 'semester' => 1, 'sks' => 2, 'deskripsi' => 'Pengenalan infrastruktur TI, hardware, software, dan jaringan'],
            ['kode_matkul' => 'MK103', 'nama_matkul' => 'Matematika Diskrit', 'semester' => 1, 'sks' => 3, 'deskripsi' => 'Logika matematika, relasi, himpunan, dan graf'],
            ['kode_matkul' => 'MK104', 'nama_matkul' => 'Bahasa Inggris Teknis', 'semester' => 1, 'sks' => 2, 'deskripsi' => 'Keterampilan komunikasi dan terminologi teknis teknologi informasi'],

            // Semester 2
            ['kode_matkul' => 'MK201', 'nama_matkul' => 'Struktur Data dan Algoritma', 'semester' => 2, 'sks' => 3, 'deskripsi' => 'Implementasi struktur data linear dan non-linear dalam pemrograman'],
            ['kode_matkul' => 'MK202', 'nama_matkul' => 'Pemrograman Berorientasi Objek', 'semester' => 2, 'sks' => 3, 'deskripsi' => 'Prinsip OOP: enkapsulasi, pewarisan, polimorfisme, dan abstraksi'],
            ['kode_matkul' => 'MK203', 'nama_matkul' => 'Sistem Basis Data', 'semester' => 2, 'sks' => 3, 'deskripsi' => 'Perancangan ERD, normalisasi, dan query SQL relasional'],
            ['kode_matkul' => 'MK204', 'nama_matkul' => 'Sistem Operasi', 'semester' => 2, 'sks' => 2, 'deskripsi' => 'Manajemen proses, memori, I/O, dan sistem berkas'],

            // Semester 3
            ['kode_matkul' => 'MK301', 'nama_matkul' => 'Pemrograman Web Lanjut', 'semester' => 3, 'sks' => 3, 'deskripsi' => 'Pengembangan aplikasi web modern fullstack dengan framework Laravel'],
            ['kode_matkul' => 'MK302', 'nama_matkul' => 'Rekayasa Perangkat Lunak', 'semester' => 3, 'sks' => 3, 'deskripsi' => 'Siklus hidup SDLC, analisis kebutuhan, dan perancangan arsitektur software'],
            ['kode_matkul' => 'MK303', 'nama_matkul' => 'Desain Antarmuka Pengguna (UI/UX)', 'semester' => 3, 'sks' => 2, 'deskripsi' => 'Prinsip desain pengalaman pengguna, prototyping, dan usability testing'],
            ['kode_matkul' => 'MK304', 'nama_matkul' => 'Jaringan Komputer', 'semester' => 3, 'sks' => 3, 'deskripsi' => 'Protokol TCP/IP, routing, subnetting, dan konfigurasi server'],

            // Semester 4
            ['kode_matkul' => 'MK401', 'nama_matkul' => 'Pemrograman Perangkat Bergerak (Mobile)', 'semester' => 4, 'sks' => 3, 'deskripsi' => 'Pengembangan aplikasi mobile native/hybrid untuk Android dan iOS'],
            ['kode_matkul' => 'MK402', 'nama_matkul' => 'Manajemen Proyek Perangkat Lunak', 'semester' => 4, 'sks' => 2, 'deskripsi' => 'Metodologi Agile/Scrum, estimasi beban kerja, dan manajemen tim proyek'],
            ['kode_matkul' => 'MK403', 'nama_matkul' => 'Pengujian dan Penjaminan Mutu Perangkat Lunak', 'semester' => 4, 'sks' => 3, 'deskripsi' => 'Metode testing automated unit, integration, dan black-box testing'],
            ['kode_matkul' => 'MK404', 'nama_matkul' => 'Komputasi Awan (Cloud Computing)', 'semester' => 4, 'sks' => 2, 'deskripsi' => 'Deployment aplikasi cloud, Docker kontainer, dan virtualisasi'],

            // Semester 5
            ['kode_matkul' => 'MK501', 'nama_matkul' => 'Kecerdasan Buatan dan Sains Data', 'semester' => 5, 'sks' => 3, 'deskripsi' => 'Penerapan algoritma machine learning dan analisis data terapan'],
            ['kode_matkul' => 'MK502', 'nama_matkul' => 'Keamanan Informasi dan Kriptografi', 'semester' => 5, 'sks' => 3, 'deskripsi' => 'Keamanan data, enkripsi, autentikasi, dan mitigasi kerentanan'],
            ['kode_matkul' => 'MK503', 'nama_matkul' => 'Integrasi Sistem dan API Terdistribusi', 'semester' => 5, 'sks' => 2, 'deskripsi' => 'Perancangan RESTful API, webhook, dan arsitektur microservices'],

            // Semester 6
            ['kode_matkul' => 'MK601', 'nama_matkul' => 'Proyek Capstone Terapan', 'semester' => 6, 'sks' => 4, 'deskripsi' => 'Pengerjaan proyek komprehensif solusi nyata berbasis industri atau mitra'],
            ['kode_matkul' => 'MK602', 'nama_matkul' => 'Kewirausahaan Berbasis Teknologi', 'semester' => 6, 'sks' => 2, 'deskripsi' => 'Model bisnis startup, monetisasi produk digital, dan pitching'],
        ];

        foreach ($matkulList as $data) {
            MataKuliah::updateOrCreate(
                ['kode_matkul' => $data['kode_matkul']],
                array_merge($data, [
                    'uuid' => (string) Str::uuid(),
                    'is_active' => true,
                ])
            );
        }
    }
}
