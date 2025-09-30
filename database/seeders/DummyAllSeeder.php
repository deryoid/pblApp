<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DummyAllSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to create all dummy data...');

        // Run seeders in correct order
        $this->call([
            DummyMahasiswaSeeder::class,
            DummyKelompokSeeder::class,
            DummyProjectSeeder::class,
            DummyAktivitasSeeder::class,
        ]);

        $this->command->info('All dummy data created successfully!');
        $this->command->info('Summary:');
        $this->command->info('- 10 Mahasiswa in TI-3A class');
        $this->command->info('- 3 Kelompok (Smart Farming, E-Commerce, Smart City)');
        $this->command->info('- Project lists and cards for each kelompok');
        $this->command->info('- Aktivitas lists (6 minggu) and cards for each kelompok');
    }
}
