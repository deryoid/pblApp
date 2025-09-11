<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kelas::create(['kelas' => '2A']);
        Kelas::create(['kelas' => '2B']);
        Kelas::create(['kelas' => '2C']);
        Kelas::create(['kelas' => '3A']);
        Kelas::create(['kelas' => '3B']);
        Kelas::create(['kelas' => '3C']);
        Kelas::create(['kelas' => '4A']);
        Kelas::create(['kelas' => '4B']);
        Kelas::create(['kelas' => '4C']);
        Kelas::create(['kelas' => '5A']);
        Kelas::create(['kelas' => '5B']);
        Kelas::create(['kelas' => '5C']);
    }
}
