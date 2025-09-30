<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyMahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing periode or create default one
        $periode = Periode::firstOrCreate(
            ['periode' => '2025/2026 Ganjil'],
            [
                'status_periode' => 'Aktif',
            ]
        );

        $mahasiswaData = [
            [
                'nim' => '230001',
                'nama_mahasiswa' => 'Ahmad Rizki Pratama',
                'email' => 'ahmad.rizki@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'nim' => '230002',
                'nama_mahasiswa' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'nim' => '230003',
                'nama_mahasiswa' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'nim' => '230004',
                'nama_mahasiswa' => 'Dewi Lestari',
                'email' => 'dewi.lestari@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'nim' => '230005',
                'nama_mahasiswa' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'nim' => '230006',
                'nama_mahasiswa' => 'Fitri Handayani',
                'email' => 'fitri.handayani@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'nim' => '230007',
                'nama_mahasiswa' => 'Gunawan Wijaya',
                'email' => 'gunawan.wijaya@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'nim' => '230008',
                'nama_mahasiswa' => 'Hana Pertiwi',
                'email' => 'hana.pertiwi@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'nim' => '230009',
                'nama_mahasiswa' => 'Irfan Hakim',
                'email' => 'irfan.hakim@example.com',
                'password' => bcrypt('password123'),
            ],
            [
                'nim' => '230010',
                'nama_mahasiswa' => 'Julia Rahmawati',
                'email' => 'julia.rahmawati@example.com',
                'password' => bcrypt('password123'),
            ],
        ];

        foreach ($mahasiswaData as $data) {
            // Create user first
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nama_user' => $data['nama_mahasiswa'],
                    'username' => strtolower(str_replace(' ', '', $data['nama_mahasiswa'])),
                    'password' => $data['password'],
                    'role' => 'mahasiswa',
                    'no_hp' => '081234567'.str_pad(substr($data['nim'], -3), 3, '0', STR_PAD_LEFT),
                    'email_verified_at' => now(),
                ]
            );

            // Create mahasiswa linked to user
            Mahasiswa::firstOrCreate(
                ['nim' => $data['nim']],
                [
                    'nama_mahasiswa' => $data['nama_mahasiswa'],
                    'user_id' => $user->id,
                ]
            );
        }

        $this->command->info('Dummy mahasiswa data created successfully!');
        $this->command->info('Created 10 mahasiswa with user accounts');
    }
}
