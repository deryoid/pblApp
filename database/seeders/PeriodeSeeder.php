<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Periode;

class PeriodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Periode::create([
            'periode'        => '25/26-1',
            'status_periode' => 'Tidak Aktif',
        ]);

        Periode::create([
            'periode'    => '25/26-2',
            'status_periode' => 'Aktif',
        ]);
    }
}
