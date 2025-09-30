<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use Illuminate\Database\Seeder;

class DummyKelompokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periode = Periode::where('periode', '2025/2026 Ganjil')->first();

        if (! $periode) {
            $this->command->error('Periode not found! Run DummyMahasiswaSeeder first.');

            return;
        }

        // Create or get kelas
        $kelas = Kelas::firstOrCreate(
            ['kelas' => 'TI-3A']
        );

        $kelompokData = [
            [
                'nama_kelompok' => 'Kelompok 1 - Smart Farming',
                'periode_id' => $periode->id,
                'link_drive' => 'https://drive.google.com/smart-farming',
            ],
            [
                'nama_kelompok' => 'Kelompok 2 - E-Commerce',
                'periode_id' => $periode->id,
                'link_drive' => 'https://drive.google.com/ecommerce',
            ],
            [
                'nama_kelompok' => 'Kelompok 3 - Smart City',
                'periode_id' => $periode->id,
                'link_drive' => 'https://drive.google.com/smart-city',
            ],
        ];

        // Get all mahasiswa
        $mahasiswa = Mahasiswa::all();

        if ($mahasiswa->count() < 10) {
            $this->command->error('Not enough mahasiswa found! Run DummyMahasiswaSeeder first.');

            return;
        }

        // Split mahasiswa into groups
        $groups = [
            $mahasiswa->take(4), // 4 mahasiswa for group 1
            $mahasiswa->slice(4, 3), // 3 mahasiswa for group 2
            $mahasiswa->slice(7, 3), // 3 mahasiswa for group 3
        ];

        foreach ($kelompokData as $index => $data) {
            $kelompok = Kelompok::firstOrCreate(
                ['nama_kelompok' => $data['nama_kelompok']],
                $data
            );

            // Add mahasiswa to kelompok
            $mahasiswaGroup = $groups[$index];

            foreach ($mahasiswaGroup as $mhs) {
                // Check if relationship already exists
                $existing = $kelompok->mahasiswas()->where('mahasiswa_id', $mhs->id)->exists();

                if (! $existing) {
                    $kelompok->mahasiswas()->attach($mhs->id, [
                        'periode_id' => $periode->id,
                        'kelas_id' => $kelas->id,
                        'role' => $index === 0 ? 'Ketua' : 'Anggota',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Dummy kelompok data created successfully!');
        $this->command->info('Kelompok 1: 4 mahasiswa (Smart Farming)');
        $this->command->info('Kelompok 2: 3 mahasiswa (E-Commerce)');
        $this->command->info('Kelompok 3: 3 mahasiswa (Smart City)');
    }
}
