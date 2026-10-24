<?php

namespace Tests\Feature;

use App\Models\Kelompok;
use App\Models\Periode;
use App\Models\ProjectCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_showcase_homepage_loads_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('PBL SHOWCASE');
        $response->assertSee('Eksplorasi Mahakarya');
    }

    public function test_showcase_api_returns_completed_projects(): void
    {
        $periode = Periode::create([
            'periode' => '2025/2026 - Genap',
            'status_periode' => 'Aktif',
        ]);

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 1 - Testing',
            'periode_id' => $periode->id,
            'semester' => 4,
            'status_bergabung' => 'terbuka',
        ]);

        $project = ProjectCard::create([
            'title' => 'Sistem IoT Air Tambak',
            'description' => 'Monitoring air tambak',
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
            'status_proyek' => 'Selesai',
            'progress' => 100,
            'skema_pbl' => 'Industri',
            'nama_mitra' => 'PT Testing',
        ]);

        $response = $this->get(route('showcase.data'));

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonFragment(['title' => 'Sistem IoT Air Tambak']);
    }

    public function test_login_page_loads_with_showcase_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('SIMEP POLITALA');
        $response->assertSee('Showcase Proyek Mahasiswa');
    }
}
