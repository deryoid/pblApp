<?php

namespace Database\Seeders;

use App\Models\Periode;
use Illuminate\Database\Seeder;

class PeriodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Periode::create([
            'periode' => '25/26-1',
            'status_periode' => 'Tidak Aktif',
        ]);

        Periode::create([
            'periode' => '25/26-2',
            'status_periode' => 'Aktif',
        ]);
    }
}
