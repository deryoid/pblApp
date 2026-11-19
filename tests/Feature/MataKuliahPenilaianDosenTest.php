<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMaster;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MataKuliahPenilaianDosenTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $dosenUser;

    private Dosen $dosen;

    private Periode $periode;

    private Kelompok $kelompok;

    private Mahasiswa $mahasiswa;

    private ProjectCard $card;

    private MataKuliah $matkul1;

    private MataKuliah $matkul2;

    private EvaluasiMaster $sesi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->periode = Periode::create([
            'periode' => '2025/2026 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->adminUser = User::create([
            'nama_user' => 'Admin Test',
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'email' => 'admin_test@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_hp' => '081234567890',
            'is_profile_complete' => true,
        ]);

        $this->dosenUser = User::create([
            'nama_user' => 'Dosen Test',
            'name' => 'Dosen Test',
            'username' => '19876543210',
            'email' => 'dosen_test@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '081234567891',
            'is_profile_complete' => true,
        ]);

        $this->dosen = Dosen::create([
            'user_id' => $this->dosenUser->id,
            'nip' => '19876543210',
            'nama_dosen' => 'Dosen Test',
            'kode_dosen' => 'DST',
            'email' => 'dosen_test@politala.ac.id',
            'status' => 'Aktif',
        ]);

        $studentUser = User::create([
            'nama_user' => 'Mahasiswa Test',
            'name' => 'Mahasiswa Test',
            'username' => '210101001',
            'email' => 'mhs@test.com',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567892',
            'is_profile_complete' => true,
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $studentUser->id,
            'nim' => '210101001',
            'nama_mahasiswa' => 'Mahasiswa Test',
            'email' => 'mhs@test.com',
            'is_profile_complete' => true,
        ]);

        $this->kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 1 Alpha',
            'periode_id' => $this->periode->id,
            'kategori' => 'Software',
        ]);

        $this->kelompok->mahasiswas()->attach($this->mahasiswa->id, [
            'role' => 'Ketua',
            'periode_id' => $this->periode->id,
        ]);

        $list = ProjectList::create([
            'kelompok_id' => $this->kelompok->id,
            'title' => 'In Progress',
            'name' => 'In Progress',
            'periode_id' => $this->periode->id,
        ]);

        $this->card = ProjectCard::create([
            'list_id' => $list->id,
            'kelompok_id' => $this->kelompok->id,
            'title' => 'Proyek IoT Smart Campus',
            'description' => 'Monitoring suhu dan kelembaban kampus',
            'skema_pbl' => 'Internal',
            'progress' => 50,
        ]);

        $this->matkul1 = MataKuliah::create([
            'kode_matkul' => 'MK01',
            'nama_matkul' => 'Pemrograman IoT',
            'semester' => 3,
            'is_active' => true,
        ]);

        $this->matkul2 = MataKuliah::create([
            'kode_matkul' => 'MK02',
            'nama_matkul' => 'Jaringan Komputer Lanjut',
            'semester' => 3,
            'is_active' => true,
        ]);

        // Attach matkul to card
        $this->card->mataKuliahs()->attach([$this->matkul1->id, $this->matkul2->id]);

        // Lecturer teaches matkul1
        $this->matkul1->dosenPengampu()->attach($this->dosenUser->id);

        $this->sesi = EvaluasiMaster::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'created_by' => $this->adminUser->id,
            'evaluator_id' => $this->adminUser->id,
            'tanggal_evaluasi' => now(),
            'status' => 'aktif',
        ]);
    }

    public function test_get_penilaian_dosen_by_project_returns_connected_courses(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.evaluasi.penilaian-dosen.show-by-project', ['project' => $this->card->uuid]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'mata_kuliahs');
    }

    public function test_dosen_can_save_grades_scoped_to_taught_mata_kuliah(): void
    {
        $payload = [
            'evaluasi_master_id' => $this->sesi->id,
            'mata_kuliah_id' => $this->matkul1->id,
            'items' => [
                $this->mahasiswa->id => [
                    'nilai' => [
                        'd_relevansi' => 85,
                        'd_kualitas' => 90,
                        'd_kreativitas' => 80,
                        'd_estetika' => 85,
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->dosenUser)
            ->postJson(route('dosen.evaluasi.project.grade.dosen', ['card' => $this->card->uuid]), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('evaluasi_dosen', [
            'project_card_id' => $this->card->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'mata_kuliah_id' => $this->matkul1->id,
            'd_relevansi' => 85,
            'd_kualitas' => 90,
        ]);
    }

    public function test_grades_can_be_filtered_by_mata_kuliah_id(): void
    {
        // Create an evaluation for matkul1
        EvaluasiDosen::create([
            'evaluasi_master_id' => $this->sesi->id,
            'project_card_id' => $this->card->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'evaluator_id' => $this->dosenUser->id,
            'mata_kuliah_id' => $this->matkul1->id,
            'd_relevansi' => 85,
            'd_kualitas' => 85,
            'd_kreativitas' => 85,
            'd_estetika' => 85,
            'nilai_akhir' => 85,
            'status' => 'submitted',
        ]);

        // Create an evaluation for matkul2
        EvaluasiDosen::create([
            'evaluasi_master_id' => $this->sesi->id,
            'project_card_id' => $this->card->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'evaluator_id' => $this->adminUser->id,
            'mata_kuliah_id' => $this->matkul2->id,
            'd_relevansi' => 70,
            'd_kualitas' => 70,
            'd_kreativitas' => 70,
            'd_estetika' => 70,
            'nilai_akhir' => 70,
            'status' => 'submitted',
        ]);

        // Filter by matkul1
        $response1 = $this->actingAs($this->adminUser)
            ->getJson(route('admin.evaluasi.penilaian-dosen.show-by-project', [
                'project' => $this->card->uuid,
                'mata_kuliah_id' => $this->matkul1->id,
            ]));

        $response1->assertStatus(200);
        $evals1 = $response1->json('evaluations');
        $this->assertCount(1, $evals1);
        $this->assertEquals($this->matkul1->id, $evals1[0]['mata_kuliah_id']);
        $this->assertEquals(85, $evals1[0]['d_relevansi']);

        // Filter by matkul2
        $response2 = $this->actingAs($this->adminUser)
            ->getJson(route('admin.evaluasi.penilaian-dosen.show-by-project', [
                'project' => $this->card->uuid,
                'mata_kuliah_id' => $this->matkul2->id,
            ]));

        $response2->assertStatus(200);
        $evals2 = $response2->json('evaluations');
        $this->assertCount(1, $evals2);
        $this->assertEquals($this->matkul2->id, $evals2[0]['mata_kuliah_id']);
        $this->assertEquals(70, $evals2[0]['d_relevansi']);
    }

    public function test_score_zero_is_accepted_and_not_ignored(): void
    {
        $payload = [
            'evaluasi_master_id' => $this->sesi->id,
            'mata_kuliah_id' => $this->matkul1->id,
            'items' => [
                $this->mahasiswa->id => [
                    'nilai' => [
                        'd_relevansi' => 0,
                        'd_kualitas' => 50,
                        'd_kreativitas' => 0,
                        'd_estetika' => 50,
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.evaluasi.project.grade.dosen', ['card' => $this->card->uuid]), $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('evaluasi_dosen', [
            'project_card_id' => $this->card->id,
            'mahasiswa_id' => $this->mahasiswa->id,
            'd_relevansi' => 0,
            'd_kreativitas' => 0,
            'd_kualitas' => 50,
        ]);
    }
}
