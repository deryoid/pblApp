<?php

namespace Tests\Feature;

use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMaster;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Models\RubrikKategori;
use App\Models\RubrikSkala;
use App\Models\User;
use Database\Seeders\EvaluationRubricSeeder;
use Database\Seeders\RubrikSkalaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RubrikPenilaianTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $evaluator;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RubrikSkalaSeeder::class);

        $this->admin = User::create([
            'nama_user' => 'Super Admin',
            'username' => 'admin_rubrik',
            'email' => 'admin@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_hp' => '081234567890',
        ]);

        $this->evaluator = User::create([
            'nama_user' => 'Dosen Evaluator',
            'username' => 'evaluator_rubrik',
            'email' => 'evaluator@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'evaluator',
            'no_hp' => '081234567891',
        ]);

        $this->student = User::create([
            'nama_user' => 'Mahasiswa User',
            'username' => 'mhs_rubrik',
            'email' => 'mhs@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567892',
        ]);
    }

    public function test_rubrik_seeder_populates_four_categories_and_thirty_two_scales(): void
    {
        $this->assertSame(4, RubrikKategori::count());
        $this->assertSame(32, RubrikSkala::count());

        $kategoriCodes = RubrikKategori::pluck('kode')->toArray();
        $this->assertContains('kesesuaian_matkul', $kategoriCodes);
        $this->assertContains('kualitas_fungsi', $kategoriCodes);
        $this->assertContains('inovasi', $kategoriCodes);
        $this->assertContains('estetik', $kategoriCodes);

        $relevansi = RubrikKategori::where('kode', 'kesesuaian_matkul')->first();
        $skalaA = $relevansi->skalas()->where('nilai_huruf', 'A')->first();
        $this->assertNotNull($skalaA);
        $this->assertEquals(80.0, (float) $skalaA->nilai_min);
        $this->assertEquals(100.0, (float) $skalaA->nilai_max);
        $this->assertSame('80 – 100', $skalaA->rentang_nilai);
    }

    public function test_admin_can_access_rubrik_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rubrik.index'));

        $response->assertStatus(200);
        $response->assertSee('Rubrik Penilaian Dinamis');
        $response->assertSee('Relevansi & Konsep');
        $response->assertSee('Kualitas & Fungsi Produk');
        $response->assertSee('Kreativitas & Inovasi');
        $response->assertSee('Estetika & Pengemasan');
        $response->assertSee('80 – 100');
        $response->assertSee('75 – 79');
        $response->assertSee('70 – 74');
    }

    public function test_api_data_returns_categories_and_grade_scales(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('rubrik.data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'categories' => [
                '*' => ['id', 'kode', 'nama', 'bobot', 'deskripsi', 'levels'],
            ],
            'scale',
        ]);

        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_evaluator_can_access_rubrik_api_data(): void
    {
        $response = $this->actingAs($this->evaluator)->getJson(route('evaluator.evaluasi.rubrik.data'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_can_update_rubrik_scale_descriptor(): void
    {
        $skala = RubrikSkala::first();
        $newDescription = 'Deskripsi narasi rubrik yang telah diperbarui oleh Admin Penguji.';

        $response = $this->actingAs($this->admin)->put(route('rubrik.update', $skala), [
            'deskripsi' => $newDescription,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rubrik_skala', [
            'id' => $skala->id,
            'deskripsi' => $newDescription,
        ]);
    }

    public function test_admin_can_update_rubrik_scale_descriptor_via_json(): void
    {
        $skala = RubrikSkala::first();
        $newDescription = 'Deskripsi narasi JSON diperbarui.';

        $response = $this->actingAs($this->admin)->putJson(route('rubrik.update', $skala), [
            'deskripsi' => $newDescription,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Deskripsi rubrik berhasil diperbarui.',
        ]);

        $this->assertDatabaseHas('rubrik_skala', [
            'id' => $skala->id,
            'deskripsi' => $newDescription,
        ]);
    }

    public function test_update_descriptor_validation_requires_text(): void
    {
        $skala = RubrikSkala::first();

        $response = $this->actingAs($this->admin)->putJson(route('rubrik.update', $skala), [
            'deskripsi' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['deskripsi']);
    }

    public function test_student_cannot_access_or_update_rubrik(): void
    {
        $skala = RubrikSkala::first();

        // Non-admin accessing admin web route is redirected back by Admin middleware
        $indexResponse = $this->actingAs($this->student)->get(route('rubrik.index'));
        $indexResponse->assertStatus(302);

        $updateResponse = $this->actingAs($this->student)->put(route('rubrik.update', $skala), [
            'deskripsi' => 'Mencoba bypass',
        ]);
        $updateResponse->assertStatus(302);
    }

    public function test_evaluasi_dosen_auto_calculates_with_four_pillars_each_twenty_five_percent(): void
    {
        $this->seed(EvaluationRubricSeeder::class);

        $periode = Periode::create([
            'periode' => '2026/2027 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $kelas = Kelas::create([
            'kelas' => 'TRKJ 4A',
            'semester' => 4,
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $this->student->id,
            'kelas_id' => $kelas->id,
            'nim' => '230411100010',
            'nama_mahasiswa' => 'Mahasiswa Test Rubrik',
            'semester' => 4,
        ]);

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 4 Pilar',
            'skema_kategori' => 'Internal',
            'semester' => 4,
            'status' => 'approved',
            'periode_id' => $periode->id,
        ]);

        $kelompok->mahasiswas()->attach($mahasiswa->id, [
            'periode_id' => $periode->id,
            'role' => 'Ketua',
        ]);

        $list = ProjectList::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
            'name' => 'Sprint 1',
            'position' => 1,
        ]);

        $card = ProjectCard::create([
            'list_id' => $list->id,
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
            'title' => 'Sistem IoT Pertanian',
            'progress' => 100,
            'position' => 1,
        ]);

        $evalMaster = EvaluasiMaster::create([
            'periode_id' => $periode->id,
            'kelompok_id' => $kelompok->id,
        ]);

        // Relevansi=80, Kualitas=90, Kreativitas=70, Estetika=80
        // Expected weighted total: (80 * 0.25) + (90 * 0.25) + (70 * 0.25) + (80 * 0.25) = 20 + 22.5 + 17.5 + 20 = 80.0
        $evaluation = EvaluasiDosen::create([
            'evaluasi_master_id' => $evalMaster->id,
            'periode_id' => $periode->id,
            'kelompok_id' => $kelompok->id,
            'mahasiswa_id' => $mahasiswa->id,
            'project_card_id' => $card->id,
            'evaluator_id' => $this->evaluator->id,
            'd_relevansi' => 80,
            'd_kualitas' => 90,
            'd_kreativitas' => 70,
            'd_estetika' => 80,
            'status' => 'submitted',
            'progress_percentage' => 100,
            'evaluation_type' => 'regular',
        ]);

        $this->assertEquals(80.0, (float) $evaluation->nilai_akhir);
        $this->assertEquals(80.0, (float) $evaluation->rata_rata);
    }

    public function test_admin_can_save_project_grade_with_four_pillars_via_endpoint(): void
    {
        $this->seed(EvaluationRubricSeeder::class);

        $periode = Periode::create([
            'periode' => '2026/2027 Genap',
            'status_periode' => 'Aktif',
        ]);

        $kelas = Kelas::create([
            'kelas' => 'TRKJ 4B',
            'semester' => 4,
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $this->student->id,
            'kelas_id' => $kelas->id,
            'nim' => '230411100011',
            'nama_mahasiswa' => 'Mahasiswa Test Endpoint',
            'semester' => 4,
        ]);

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Proyek 4 Pilar',
            'skema_kategori' => 'Internal',
            'semester' => 4,
            'status' => 'approved',
            'periode_id' => $periode->id,
        ]);

        $kelompok->mahasiswas()->attach($mahasiswa->id, [
            'periode_id' => $periode->id,
            'role' => 'Ketua',
        ]);

        $list = ProjectList::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
            'name' => 'Backlog Proyek',
            'position' => 1,
        ]);

        $card = ProjectCard::create([
            'list_id' => $list->id,
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
            'title' => 'Aplikasi Monitoring Smart Campus',
            'progress' => 100,
            'position' => 1,
        ]);

        $evalMaster = EvaluasiMaster::create([
            'periode_id' => $periode->id,
            'kelompok_id' => $kelompok->id,
        ]);

        $payload = [
            'evaluasi_master_id' => $evalMaster->id,
            'items' => [
                $mahasiswa->id => [
                    'nilai' => [
                        'd_relevansi' => 85,
                        'd_kualitas' => 85,
                        'd_kreativitas' => 85,
                        'd_estetika' => 85,
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->postJson(
            route('admin.evaluasi.project.grade.dosen', ['card' => $card]),
            $payload
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $saved = EvaluasiDosen::where('mahasiswa_id', $mahasiswa->id)
            ->where('project_card_id', $card->id)
            ->first();

        $this->assertNotNull($saved);
        $this->assertEquals(85, $saved->d_relevansi);
        $this->assertEquals(85, $saved->d_kualitas);
        $this->assertEquals(85, $saved->d_kreativitas);
        $this->assertEquals(85, $saved->d_estetika);
        $this->assertEquals(85.0, (float) $saved->nilai_akhir);
    }
}
