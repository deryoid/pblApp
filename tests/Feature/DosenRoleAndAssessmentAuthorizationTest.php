<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DosenRoleAndAssessmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $dosenPengampu;

    protected User $dosenPembimbing;

    protected User $dosenPenilai;

    protected User $dosenLain;

    protected User $mahasiswaUser;

    protected Periode $periode;

    protected Kelompok $kelompok;

    protected ProjectList $list;

    protected ProjectCard $card;

    protected MataKuliah $mataKuliah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama_user' => 'Administrator PBL',
            'username' => 'admin_pbl',
            'email' => 'admin@politala.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'no_hp' => '081234567890',
        ]);

        $this->dosenPengampu = User::create([
            'nama_user' => 'Dr. Pengampu MK, M.Kom',
            'username' => '198001012005011001',
            'email' => 'pengampu@politala.ac.id',
            'password' => Hash::make('123456'),
            'role' => 'dosen',
            'no_hp' => '081234567891',
        ]);

        $this->dosenPembimbing = User::create([
            'nama_user' => 'Ir. Pembimbing Proyek, M.T',
            'username' => '198102022006021002',
            'email' => 'pembimbing@politala.ac.id',
            'password' => Hash::make('123456'),
            'role' => 'dosen',
            'no_hp' => '081234567892',
        ]);

        $this->dosenPenilai = User::create([
            'nama_user' => 'Dra. Penilai Sidang, M.Cs',
            'username' => '198203032007032003',
            'email' => 'penilai@politala.ac.id',
            'password' => Hash::make('123456'),
            'role' => 'dosen',
            'no_hp' => '081234567893',
        ]);

        $this->dosenLain = User::create([
            'nama_user' => 'Dosen Luar Proyek, M.Kom',
            'username' => '198304042008041004',
            'email' => 'luar@politala.ac.id',
            'password' => Hash::make('123456'),
            'role' => 'dosen',
            'no_hp' => '081234567894',
        ]);

        $this->mahasiswaUser = User::create([
            'nama_user' => 'Mahasiswa PBL 1',
            'username' => '210101001',
            'email' => 'mhs1@politala.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567895',
        ]);

        $kelas = Kelas::create([
            'kelas' => 'TRKJ 4A',
        ]);

        $mhs = Mahasiswa::create([
            'user_id' => $this->mahasiswaUser->id,
            'nim' => '210101001',
            'nama_mahasiswa' => 'Mahasiswa PBL 1',
            'kelas_id' => $kelas->id,
            'angkatan' => 2024,
            'jenis_kelamin' => 'L',
            'is_profile_complete' => true,
        ]);

        $this->periode = Periode::create([
            'periode' => '2026/2027 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 1 Smart Campus',
            'periode_id' => $this->periode->id,
            'creator_id' => $this->mahasiswaUser->id,
        ]);
        $this->kelompok->mahasiswas()->attach($mhs->id, ['periode_id' => $this->periode->id]);

        $this->list = ProjectList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'To Do Proyek',
            'position' => 0,
        ]);

        $this->mataKuliah = MataKuliah::create([
            'kode_matkul' => 'MK401',
            'nama_matkul' => 'Rekayasa Perangkat Lunak',
            'semester' => 4,
            'sks' => 3,
            'is_active' => true,
        ]);
        $this->mataKuliah->dosenPengampu()->attach($this->dosenPengampu->id);

        $this->card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Implementasi Sistem IoT Smart Room',
            'status_proyek' => 'Proses',
            'status_proposal' => 'pending',
            'created_by' => $this->mahasiswaUser->id,
        ]);
    }

    /**
     * Admin dapat membuat akun pengguna dengan role valid (admin, dosen, mahasiswa),
     * tetapi role selain itu (misalnya 'evaluator') ditolak.
     */
    public function test_user_management_strictly_enforces_three_roles(): void
    {
        // 1. Role valid: dosen
        $resDosen = $this->actingAs($this->admin)->post('/admin/user', [
            'nama_user' => 'Dosen Baru',
            'username' => '199001012019011001',
            'email' => 'dosenbaru@politala.ac.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'dosen',
            'no_hp' => '081234000111',
        ]);
        $resDosen->assertRedirect('/admin/user');
        $this->assertDatabaseHas('users', ['username' => '199001012019011001', 'role' => 'dosen']);

        // 2. Role tidak valid: evaluator
        $resInvalid = $this->actingAs($this->admin)->post('/admin/user', [
            'nama_user' => 'Role Invalid',
            'username' => '199001012019011099',
            'email' => 'invalid@politala.ac.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'evaluator',
            'no_hp' => '081234000999',
        ]);
        $resInvalid->assertSessionHasErrors('role');
    }

    /**
     * Dosen login menggunakan NIP otomatis diarahkan ke /dosen dan memiliki role dosen.
     */
    public function test_dosen_login_redirects_to_dosen_dashboard_with_dosen_role(): void
    {
        $response = $this->post(route('login.attempt'), [
            'username' => '198001012005011001',
            'password' => '123456',
        ]);

        $response->assertRedirect('/dosen');
        $this->assertSame('dosen', auth()->user()->role);
    }

    /**
     * Admin berwenang menilai semua proyek tanpa batasan.
     */
    public function test_admin_can_evaluate_any_project_card(): void
    {
        $this->assertTrue($this->card->canBeAssessedBy($this->admin));

        $mhs = $this->kelompok->mahasiswas->first();

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.evaluasi.project.grade.dosen', ['card' => $this->card->id]), [
                'items' => [
                    $mhs->id => [
                        'nilai' => [
                            'd_relevansi' => 85,
                            'd_kualitas' => 88,
                            'd_kreativitas' => 90,
                            'd_estetika' => 86,
                        ],
                    ],
                ],
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    /**
     * Dosen pengampu mata kuliah terkait berwenang menilai proyek.
     */
    public function test_dosen_pengampu_of_linked_course_can_evaluate_project(): void
    {
        // Hubungkan mata kuliah ke kartu proyek
        $this->card->mataKuliahs()->attach($this->mataKuliah->id);

        $this->assertTrue($this->card->canBeAssessedBy($this->dosenPengampu));

        $mhs = $this->kelompok->mahasiswas->first();

        $response = $this->actingAs($this->dosenPengampu)
            ->postJson(route('dosen.evaluasi.penilaian.dosen.save', ['project' => $this->card->id]), [
                'items' => [
                    $mhs->id => [
                        'nilai' => [
                            'd_relevansi' => 90,
                            'd_kualitas' => 85,
                            'd_kreativitas' => 88,
                            'd_estetika' => 92,
                        ],
                    ],
                ],
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    /**
     * Dosen yang tidak terkait (bukan pengampu, pembimbing, atau penilai) DITOLAK saat menilai (403 Forbidden).
     */
    public function test_unauthorized_dosen_cannot_evaluate_project_and_receives_403(): void
    {
        $this->assertFalse($this->card->canBeAssessedBy($this->dosenLain));

        $mhs = $this->kelompok->mahasiswas->first();

        $response = $this->actingAs($this->dosenLain)
            ->postJson(route('dosen.evaluasi.penilaian.dosen.save', ['project' => $this->card->id]), [
                'items' => [
                    $mhs->id => [
                        'nilai' => [
                            'd_relevansi' => 70,
                            'd_kualitas' => 70,
                            'd_kreativitas' => 70,
                            'd_estetika' => 70,
                        ],
                    ],
                ],
            ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    /**
     * Dosen yang ditugaskan sebagai pembimbing (dosen_penentu_id) berwenang menilai.
     */
    public function test_dosen_pembimbing_assigned_to_project_can_evaluate(): void
    {
        $this->card->dosen_penentu_id = $this->dosenPembimbing->id;
        $this->card->save();

        $this->assertTrue($this->card->canBeAssessedBy($this->dosenPembimbing));

        $mhs = $this->kelompok->mahasiswas->first();

        $response = $this->actingAs($this->dosenPembimbing)
            ->postJson(route('dosen.evaluasi.penilaian.dosen.save', ['project' => $this->card->id]), [
                'items' => [
                    $mhs->id => [
                        'nilai' => [
                            'd_relevansi' => 80,
                            'd_kualitas' => 82,
                            'd_kreativitas' => 84,
                            'd_estetika' => 80,
                        ],
                    ],
                ],
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    /**
     * Dosen yang ditugaskan sebagai penilai khusus (dosenPenilai) berwenang menilai.
     */
    public function test_dosen_penilai_assigned_to_project_can_evaluate(): void
    {
        $this->card->dosenPenilai()->create([
            'dosen_id' => $this->dosenPenilai->id,
            'peran' => 'penilai',
        ]);

        $this->assertTrue($this->card->canBeAssessedBy($this->dosenPenilai));

        $mhs = $this->kelompok->mahasiswas->first();

        $response = $this->actingAs($this->dosenPenilai)
            ->postJson(route('dosen.evaluasi.penilaian.dosen.save', ['project' => $this->card->id]), [
                'items' => [
                    $mhs->id => [
                        'nilai' => [
                            'd_relevansi' => 85,
                            'd_kualitas' => 85,
                            'd_kreativitas' => 85,
                            'd_estetika' => 85,
                        ],
                    ],
                ],
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    /**
     * Admin dapat menghubungkan dan melepas mata kuliah pada kartu proyek mahasiswa.
     */
    public function test_admin_can_attach_and_detach_mata_kuliah_to_project_card(): void
    {
        // 1. Dosen Pengampu awalnya tidak bisa menilai karena MK belum dihubungkan
        $this->assertFalse($this->card->canBeAssessedBy($this->dosenPengampu));

        // 2. Admin menghubungkan Mata Kuliah ke kartu proyek
        $attachRes = $this->actingAs($this->admin)->postJson(
            route('project.mata-kuliah.attach', ['card' => $this->card->id]),
            ['mata_kuliah_id' => $this->mataKuliah->id]
        );

        $attachRes->assertOk();
        $attachRes->assertJson(['success' => true]);
        $this->assertTrue($this->card->fresh()->mataKuliahs->contains($this->mataKuliah->id));

        // 3. Setelah dihubungkan, dosen pengampu otomatis berwenang menilai
        $this->assertTrue($this->card->fresh()->canBeAssessedBy($this->dosenPengampu));

        // 4. Admin melepas Mata Kuliah dari kartu proyek
        $detachRes = $this->actingAs($this->admin)->deleteJson(
            route('project.mata-kuliah.detach', ['card' => $this->card->id, 'mataKuliah' => $this->mataKuliah->id])
        );

        $detachRes->assertOk();
        $detachRes->assertJson(['success' => true]);
        $this->assertFalse($this->card->fresh()->mataKuliahs->contains($this->mataKuliah->id));

        // 5. Setelah dilepas, dosen pengampu kembali tidak berwenang menilai
        $this->assertFalse($this->card->fresh()->canBeAssessedBy($this->dosenPengampu));
    }
}
