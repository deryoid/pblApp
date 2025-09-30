<?php

namespace Database\Seeders;

use App\Models\Kelompok;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use Illuminate\Database\Seeder;

class DummyProjectSeeder extends Seeder
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
            // Create Project Lists for each kelompok
            $projectLists = [
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Perencanaan',
                    'position' => 1,
                ],
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Pengembangan',
                    'position' => 2,
                ],
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Testing',
                    'position' => 3,
                ],
                [
                    'kelompok_id' => $kelompok->id,
                    'periode_id' => $kelompok->periode_id,
                    'name' => 'Deploy',
                    'position' => 4,
                ],
            ];

            foreach ($projectLists as $listData) {
                $projectList = ProjectList::firstOrCreate($listData);

                // Create Project Cards for each list
                $cardsData = [];

                switch ($kelompok->nama_kelompok) {
                    case 'Kelompok 1 - Smart Farming':
                        $cardsData = [
                            [
                                'list_id' => $projectList->id,
                                'title' => 'Riset Kebutuhan Sistem',
                                'description' => 'Menganalisis kebutuhan sistem monitoring hidroponik',
                                'progress' => 100,
                                'labels' => ['riset', 'analisis'],
                                'position' => 1,
                            ],
                            [
                                'list_id' => $projectList->id,
                                'title' => 'Desain Database',
                                'description' => 'Merancang struktur database untuk sensor data',
                                'progress' => 80,
                                'labels' => ['database', 'design'],
                                'position' => 2,
                            ],
                            [
                                'list_id' => $projectList->id,
                                'title' => 'Implementasi IoT Sensors',
                                'description' => 'Pemasangan dan konfigurasi sensor IoT',
                                'progress' => 60,
                                'labels' => ['iot', 'hardware'],
                                'position' => 3,
                            ],
                        ];
                        break;

                    case 'Kelompok 2 - E-Commerce':
                        $cardsData = [
                            [
                                'list_id' => $projectList->id,
                                'title' => 'User Research',
                                'description' => 'Mengumpulkan kebutuhan user UMKM',
                                'progress' => 100,
                                'labels' => ['research', 'ux'],
                                'position' => 1,
                            ],
                            [
                                'list_id' => $projectList->id,
                                'title' => 'UI/UX Design',
                                'description' => 'Membuat desain interface aplikasi',
                                'progress' => 90,
                                'labels' => ['design', 'ui', 'ux'],
                                'position' => 2,
                            ],
                            [
                                'list_id' => $projectList->id,
                                'title' => 'Backend Development',
                                'description' => 'Mengembangkan API dan database',
                                'progress' => 70,
                                'labels' => ['backend', 'api'],
                                'position' => 3,
                            ],
                        ];
                        break;

                    case 'Kelompok 3 - Smart City':
                        $cardsData = [
                            [
                                'list_id' => $projectList->id,
                                'title' => 'Analisis Kebutuhan',
                                'description' => 'Studi kebutuhan sistem parkir smart city',
                                'progress' => 100,
                                'labels' => ['analysis', 'planning'],
                                'position' => 1,
                            ],
                            [
                                'list_id' => $projectList->id,
                                'title' => 'Mobile App Development',
                                'description' => 'Pengembangan aplikasi mobile untuk pengguna',
                                'progress' => 75,
                                'labels' => ['mobile', 'development'],
                                'position' => 2,
                            ],
                            [
                                'list_id' => $projectList->id,
                                'title' => 'Integration Testing',
                                'description' => 'Integrasi dengan sistem parkir existing',
                                'progress' => 40,
                                'labels' => ['testing', 'integration'],
                                'position' => 3,
                            ],
                        ];
                        break;
                }

                foreach ($cardsData as $cardData) {
                    $cardData['kelompok_id'] = $kelompok->id;
                    $cardData['periode_id'] = $kelompok->periode_id;
                    $cardData['created_by'] = 1;
                    $cardData['updated_by'] = 1;

                    // Adjust progress based on project list
                    if ($projectList->name === 'Perencanaan') {
                        $cardData['progress'] = 100;
                    } elseif ($projectList->name === 'Pengembangan') {
                        $cardData['progress'] = min($cardData['progress'], 90);
                    } elseif ($projectList->name === 'Testing') {
                        $cardData['progress'] = min($cardData['progress'], 70);
                    } elseif ($projectList->name === 'Deploy') {
                        $cardData['progress'] = min($cardData['progress'], 30);
                    }

                    ProjectCard::firstOrCreate(
                        [
                            'list_id' => $cardData['list_id'],
                            'title' => $cardData['title'],
                        ],
                        $cardData
                    );
                }
            }
        }

        $this->command->info('Dummy project lists and cards created successfully!');
    }
}
