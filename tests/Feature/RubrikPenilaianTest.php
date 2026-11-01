<?php

namespace Tests\Feature;

use App\Models\RubrikKategori;
use App\Models\RubrikSkala;
use App\Models\User;
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
}
