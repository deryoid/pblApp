<?php

namespace Database\Seeders;

use App\Models\AktivitasCard;
use App\Models\AktivitasList;
use App\Models\Kelompok;
use Illuminate\Database\Seeder;

class DummyAktivitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelompoks = Kelompok::all();

        if ($kelompoks->isEmpty()) {
            $this->command->error('No kelompok found! Run DummyKelompokSeeder first.');

            return;
        }

        foreach ($kelompoks as $kelompok) {
            // Create Aktivitas Lists (Mingguan) for each kelompok
            $aktivitasLists = [
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Minggu 1 - Perencanaan Awal',
                    'rentang_tanggal' => '2025-09-01 to 2025-09-07',
                    'position' => 1,
                ],
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Minggu 2 - Implementasi',
                    'rentang_tanggal' => '2025-09-08 to 2025-09-14',
                    'position' => 2,
                ],
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Minggu 3 - Development',
                    'rentang_tanggal' => '2025-09-15 to 2025-09-21',
                    'position' => 3,
                ],
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Minggu 4 - Testing',
                    'rentang_tanggal' => '2025-09-22 to 2025-09-28',
                    'position' => 4,
                ],
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Minggu 5 - Finalisasi',
                    'rentang_tanggal' => '2025-09-29 to 2025-10-05',
                    'position' => 5,
                ],
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Minggu 6 - Presentasi',
                    'rentang_tanggal' => '2025-10-06 to 2025-10-12',
                    'position' => 6,
                ],
            ];

            foreach ($aktivitasLists as $listData) {
                $aktivitasList = AktivitasList::firstOrCreate($listData);

                // Create Aktivitas Cards for each list
                $cardsData = [];

                switch ($kelompok->nama_kelompok) {
                    case 'Kelompok 1 - Smart Farming':
                        $cardsData = [
                            [
                                'list_aktivitas_id' => $aktivitasList->id,
                                'description' => 'Mempelajari sistem hidroponik existing',
                                'bukti_kegiatan' => 'smart_farming_research.pdf',
                                'position' => 1,
                            ],
                            [
                                'list_aktivitas_id' => $aktivitasList->id,
                                'description' => 'Persiapan perangkat keras sensor',
                                'bukti_kegiatan' => 'sensor_setup.jpg',
                                'position' => 2,
                            ],
                            [
                                'list_aktivitas_id' => $aktivitasList->id,
                                'description' => 'Pengembangan aplikasi monitoring',
                                'bukti_kegiatan' => 'monitoring_app.py',
                                'position' => 3,
                            ],
                        ];
                        break;

                    case 'Kelompok 2 - E-Commerce':
                        $cardsData = [
                            [
                                'list_aktivitas_id' => $aktivitasList->id,
                                'description' => 'Riset pasar e-commerce untuk UMKM',
                                'bukti_kegiatan' => 'market_research.pdf',
                                'position' => 1,
                            ],
                            [
                                'list_aktivitas_id' => $aktivitasList->id,
                                'description' => 'Pembuatan wireframe aplikasi',
                                'bukti_kegiatan' => 'wireframe.fig',
                                'position' => 2,
                            ],
                            [
                                'list_aktivitas_id' => $aktivitasList->id,
                                'description' => 'Pengembangan tampilan aplikasi',
                                'bukti_kegiatan' => 'frontend_code.zip',
                                'position' => 3,
                            ],
                        ];
                        break;

                    case 'Kelompok 3 - Smart City':
                        $cardsData = [
                            [
                                'list_aktivitas_id' => $aktivitasList->id,
                                'description' => 'Survei lokasi parkir yang akan dipasang sistem',
                                'bukti_kegiatan' => 'site_survey_report.pdf',
                                'position' => 1,
                            ],
                            [
                                'list_aktivitas_id' => $aktivitasList->id,
                                'description' => 'Perancangan arsitektur sistem',
                                'bukti_kegiatan' => 'system_design.pdf',
                                'position' => 2,
                            ],
                            [
                                'list_aktivitas_id' => $aktivitasList->id,
                                'description' => 'Pengembangan aplikasi mobile',
                                'bukti_kegiatan' => 'mobile_app.apk',
                                'position' => 3,
                            ],
                        ];
                        break;
                }

                foreach ($cardsData as $cardData) {
                    $cardData['kelompok_id'] = $kelompok->id;
                    $cardData['periode_id'] = $kelompok->periode_id;
                    $cardData['tanggal_aktivitas'] = now()->addDays($cardData['position'] - 1);
                    $cardData['created_by'] = 1;
                    $cardData['updated_by'] = 1;

                    AktivitasCard::firstOrCreate(
                        [
                            'list_aktivitas_id' => $cardData['list_aktivitas_id'],
                            'description' => $cardData['description'],
                        ],
                        $cardData
                    );
                }
            }
        }

        $this->command->info('Dummy aktivitas lists and cards created successfully!');
        $this->command->info('Created 6 minggu for each kelompok with weekly activities');
    }
}
