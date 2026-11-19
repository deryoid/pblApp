<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectDosenPenilai;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectProposalVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $dosenPengampu;

    protected User $dosenPenilai;

    protected User $dosenLain;

    protected User $studentUser;

    protected Mahasiswa $mahasiswa;

    protected Kelompok $kelompok;

    protected Periode $periode;

    protected Kelas $kelas;

    protected ProjectList $list;

    protected MataKuliah $mataKuliah;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->periode = Periode::create([
            'periode' => '2026/2027 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->kelas = Kelas::create([
            'kelas' => 'TRKJ 4A',
            'semester' => 4,
        ]);

        $this->admin = User::create([
            'nama_user' => 'Super Admin PBL',
            'username' => 'admin_pbl',
            'email' => 'adminpbl@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_hp' => '081234567891',
        ]);

        $this->dosenPengampu = User::create([
            'nama_user' => 'Dr. Budi Dosen Pengampu',
            'username' => 'budi_pengampu',
            'email' => 'budi_pengampu@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '081234567892',
        ]);

        $this->dosenPenilai = User::create([
            'nama_user' => 'Dr. Siti Dosen Penilai',
            'username' => 'siti_penilai',
            'email' => 'siti_penilai@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '081234567893',
        ]);

        $this->dosenLain = User::create([
            'nama_user' => 'Dosen Tidak Terkait',
            'username' => 'dosen_lain',
            'email' => 'dosen_lain@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '081234567894',
        ]);

        $this->studentUser = User::create([
            'nama_user' => 'Andi Mahasiswa',
            'username' => 'andi_mhs',
            'email' => 'andi@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567895',
            'is_profile_complete' => true,
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $this->studentUser->id,
            'nama_mahasiswa' => 'Andi Mahasiswa',
            'nim' => '202610101',
            'kelas_id' => $this->kelas->id,
            'jenis_kelamin' => 'Laki-laki',
            'is_profile_complete' => true,
        ]);

        $this->kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Alpha PBL',
            'periode_id' => $this->periode->id,
            'kelas_id' => $this->kelas->id,
            'skema_kategori' => 'Internal',
            'status' => 'Aktif',
            'created_by' => $this->studentUser->id,
        ]);

        $this->kelompok->mahasiswas()->attach($this->mahasiswa->id, ['periode_id' => $this->periode->id]);

        $this->list = ProjectList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Perencanaan',
            'position' => 0,
        ]);

        $this->mataKuliah = MataKuliah::create([
            'kode_matkul' => 'MK401',
            'nama_matkul' => 'Rekayasa Perangkat Lunak Lanjut',
            'sks' => 3,
            'semester' => 4,
            'is_active' => true,
        ]);

        $this->mataKuliah->dosenPengampu()->attach($this->dosenPengampu->id);
    }

    public function test_student_can_upload_optional_proposal_without_acc_blocking(): void
    {
        $file = UploadedFile::fake()->create('proposal_proyek_alpha.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->studentUser)
            ->post(route('proyek.cards.store'), [
                'title' => 'Sistem Informasi Manajemen Aset',
                'description' => 'Aplikasi web untuk tracking inventaris kampus.',
                'list' => $this->list->id,
                'skema_pbl' => 'Internal',
                'mata_kuliah_ids' => [$this->mataKuliah->id],
                'proposal_file' => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $card = ProjectCard::where('title', 'Sistem Informasi Manajemen Aset')->first();
        $this->assertNotNull($card);
        $this->assertNotNull($card->proposal_file_path);
        Storage::disk('public')->assertExists($card->proposal_file_path);
    }

    public function test_student_can_create_internal_project_without_proposal_file(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->post(route('proyek.cards.store'), [
                'title' => 'Proyek Tanpa Proposal Awal',
                'description' => 'Proyek internal yang dimulai tanpa lampiran file proposal.',
                'list' => $this->list->id,
                'skema_pbl' => 'Internal',
                'mata_kuliah_ids' => [$this->mataKuliah->id],
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $card = ProjectCard::where('title', 'Proyek Tanpa Proposal Awal')->first();
        $this->assertNotNull($card);
        $this->assertNull($card->proposal_file_path);
    }

    public function test_admin_can_assign_dosen_penilai_to_project_card(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart IoT Farming',
            'skema_pbl' => 'Internal',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);

        $response = $this->actingAs($this->admin)
            ->postJson(route('project.dosen-penilai.store', ['card' => $card->id]), [
                'dosen_id' => $this->dosenPenilai->id,
                'mata_kuliah_id' => $this->mataKuliah->id,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('project_dosen_penilai', [
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPenilai->id,
        ]);
    }

    public function test_non_admin_cannot_assign_dosen_penilai(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart IoT Farming',
            'created_by' => $this->studentUser->id,
        ]);

        $response = $this->actingAs($this->studentUser)
            ->postJson(route('project.dosen-penilai.store', ['card' => $card->id]), [
                'dosen_id' => $this->dosenPenilai->id,
            ]);

        $response->assertForbidden();
    }

    public function test_admin_can_remove_dosen_penilai(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart IoT Farming',
            'created_by' => $this->studentUser->id,
        ]);

        $penilai = ProjectDosenPenilai::create([
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPenilai->id,
            'status_penilaian' => 'belum',
            'assigned_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('project.dosen-penilai.destroy', ['card' => $card->id, 'penilai' => $penilai->id]));

        $response->assertOk();
        $this->assertDatabaseMissing('project_dosen_penilai', ['id' => $penilai->id]);
    }

    public function test_get_status_endpoint_returns_involved_dosen_cleanly(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Analytics Dashboard',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);

        $response = $this->actingAs($this->dosenPengampu)
            ->getJson(route('project.verifikasi.status', ['card' => $card->id]));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'card' => ['id', 'title'],
            'involved_dosen',
            'is_admin',
        ]);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_admin_and_evaluator_show_project_includes_involved_dosen(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Virtual Reality Classroom',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);

        // Admin show project
        $resAdmin = $this->actingAs($this->admin)
            ->getJson(route('admin.evaluasi.project.show', ['card' => $card->id]));

        $resAdmin->assertOk();
        $resAdmin->assertJsonStructure([
            'success',
            'project',
            'involved_dosen',
            'can_assess',
            'is_admin',
            'available_dosen',
        ]);
        $this->assertTrue($resAdmin->json('is_admin'));
        $this->assertTrue($resAdmin->json('can_assess'));

        // Dosen show project
        $resEval = $this->actingAs($this->dosenPengampu)
            ->getJson(route('dosen.evaluasi.project.show', ['card' => $card->id]));

        $resEval->assertOk();
        $resEval->assertJsonStructure([
            'success',
            'project',
            'involved_dosen',
            'can_assess',
            'is_admin',
        ]);
        $this->assertTrue($resEval->json('can_assess'));
    }

    public function test_mahasiswa_proyek_index_renders_project_card(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart Water Quality',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);

        $response = $this->actingAs($this->studentUser)
            ->get(route('proyek.index'));

        $response->assertOk();
        $response->assertSee('Proyek Smart Water Quality');
    }

    public function test_admin_can_assign_dosen_as_pembimbing(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Autonomous Drone',
            'created_by' => $this->studentUser->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('project.dosen.assign', ['card' => $card->id]), [
                'dosen_id' => $this->dosenLain->id,
                'peran' => 'pembimbing',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $card->refresh();
        $this->assertEquals($this->dosenLain->id, $card->dosen_penentu_id);
    }

    public function test_admin_can_remove_dosen_from_pembimbing(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart Greenhouse',
            'dosen_penentu_id' => $this->dosenLain->id,
            'created_by' => $this->studentUser->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('project.dosen.remove', ['card' => $card->id]), [
                'dosen_id' => $this->dosenLain->id,
                'peran_tipe' => 'pembimbing',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $card->refresh();
        $this->assertNull($card->dosen_penentu_id);
    }

    public function test_admin_can_assign_and_remove_penilai_via_unified_endpoints(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart Aquaculture',
            'created_by' => $this->studentUser->id,
        ]);

        // Assign
        $assignRes = $this->actingAs($this->admin)
            ->postJson(route('project.dosen.assign', ['card' => $card->id]), [
                'dosen_id' => $this->dosenPenilai->id,
                'peran' => 'penilai',
                'mata_kuliah_id' => $this->mataKuliah->id,
            ]);

        $assignRes->assertOk();
        $assignRes->assertJson(['success' => true]);

        $this->assertDatabaseHas('project_dosen_penilai', [
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPenilai->id,
            'mata_kuliah_id' => $this->mataKuliah->id,
        ]);

        // Remove
        $removeRes = $this->actingAs($this->admin)
            ->postJson(route('project.dosen.remove', ['card' => $card->id]), [
                'dosen_id' => $this->dosenPenilai->id,
                'peran_tipe' => 'penilai',
            ]);

        $removeRes->assertOk();
        $removeRes->assertJson(['success' => true]);

        $this->assertDatabaseMissing('project_dosen_penilai', [
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPenilai->id,
        ]);
    }

    public function test_admin_can_update_dosen_penentu_via_update_project(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek IoT Smart Bin',
            'created_by' => $this->studentUser->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.evaluasi.project.update', ['card' => $card->id]), [
                'title' => 'Proyek IoT Smart Bin Updated',
                'dosen_penentu_id' => $this->dosenPengampu->id,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $card->refresh();
        $this->assertEquals($this->dosenPengampu->id, $card->dosen_penentu_id);
    }
}
