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
use App\Models\ProjectVerifikasiDosen;
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
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $this->studentUser->id,
            'nim' => '202604999',
            'nama_mahasiswa' => 'Andi Mahasiswa',
            'kelas_id' => $this->kelas->id,
            'semester' => 4,
            'status' => 'Aktif',
            'is_profile_complete' => true,
        ]);

        $this->kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 10 Proyek',
            'nomor_kelompok' => 10,
            'kelas_id' => $this->kelas->id,
            'periode_id' => $this->periode->id,
            'skema_kategori' => 'Internal',
            'semester' => 4,
            'status_kelompok' => 'Disetujui',
            'ketua_id' => $this->mahasiswa->id,
        ]);

        $this->kelompok->mahasiswas()->attach($this->mahasiswa->id, [
            'role' => 'Ketua',
            'periode_id' => $this->periode->id,
        ]);

        $this->list = ProjectList::create([
            'name' => 'Backlog Proyek',
            'position' => 0,
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
        ]);

        $this->mataKuliah = MataKuliah::create([
            'kode_matkul' => 'MKPBL401',
            'nama_matkul' => 'Rekayasa Perangkat Lunak PBL',
            'sks' => 4,
            'semester' => 4,
            'deskripsi' => 'Pengembangan perangkat lunak berbasis PBL',
        ]);

        $this->mataKuliah->dosenPengampu()->attach($this->dosenPengampu->id);
    }

    public function test_card_proposal_starts_as_pending_when_uploaded_by_student(): void
    {
        $file = UploadedFile::fake()->create('proposal_proyek.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->studentUser)
            ->post(route('proyek.cards.store'), [
                'list_id' => $this->list->id,
                'title' => 'Sistem Informasi Manajemen Aset',
                'description' => 'Aplikasi internal untuk inventaris barang.',
                'skema_pbl' => 'Internal',
                'mata_kuliah_ids' => [$this->mataKuliah->id],
                'proposal_file' => $file,
            ]);

        $response->assertRedirect();

        $card = ProjectCard::where('title', 'Sistem Informasi Manajemen Aset')->first();
        $this->assertNotNull($card);
        $this->assertNotNull($card->proposal_file_path);
        $this->assertEquals('pending', $card->status_proposal);

        // Pastikan dosen pengampu MK tersinkronisasi ke project_verifikasi_dosen
        $verif = ProjectVerifikasiDosen::where('project_card_id', $card->id)
            ->where('dosen_id', $this->dosenPengampu->id)
            ->first();

        $this->assertNotNull($verif);
        $this->assertEquals('pending', $verif->status);
    }

    public function test_admin_can_assign_dosen_penilai_to_project_card(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart IoT Farming',
            'status_proposal' => 'pending',
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

        // Verifikasi dosen penilai juga otomatis tersinkron di project_verifikasi_dosen
        $this->assertDatabaseHas('project_verifikasi_dosen', [
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPenilai->id,
            'status' => 'pending',
        ]);
    }

    public function test_non_admin_cannot_assign_dosen_penilai(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart IoT Farming',
            'status_proposal' => 'pending',
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
            'status_proposal' => 'pending',
            'created_by' => $this->studentUser->id,
        ]);

        $penilai = ProjectDosenPenilai::create([
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPenilai->id,
            'status_penilaian' => 'belum',
            'assigned_by' => $this->admin->id,
        ]);

        $card->syncVerifikasiDosen();

        $this->assertDatabaseHas('project_verifikasi_dosen', [
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPenilai->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('project.dosen-penilai.destroy', ['card' => $card->id, 'penilai' => $penilai->id]));

        $response->assertOk();
        $this->assertDatabaseMissing('project_dosen_penilai', ['id' => $penilai->id]);
        $this->assertDatabaseMissing('project_verifikasi_dosen', [
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPenilai->id,
        ]);
    }

    public function test_involved_dosen_can_acc_proposal(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Web E-Commerce',
            'status_proposal' => 'pending',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);
        $card->syncVerifikasiDosen();

        $response = $this->actingAs($this->dosenPengampu)
            ->postJson(route('project.verifikasi.acc', ['card' => $card->id]), [
                'status' => 'acc',
                'catatan' => 'Proposal sangat baik dan relevan.',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => 'acc',
            'status_proposal' => 'disetujui',
        ]);

        $verif = ProjectVerifikasiDosen::where('project_card_id', $card->id)
            ->where('dosen_id', $this->dosenPengampu->id)
            ->first();

        $this->assertEquals('acc', $verif->status);
        $this->assertEquals('Proposal sangat baik dan relevan.', $verif->catatan);
        $this->assertNotNull($verif->verified_at);

        $card->refresh();
        $this->assertEquals('disetujui', $card->status_proposal);
    }

    public function test_uninvolved_dosen_cannot_acc_proposal(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Web E-Commerce',
            'status_proposal' => 'pending',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);
        $card->syncVerifikasiDosen();

        $response = $this->actingAs($this->dosenLain)
            ->postJson(route('project.verifikasi.acc', ['card' => $card->id]), [
                'status' => 'acc',
            ]);

        $response->assertForbidden();
    }

    public function test_all_dosen_acc_updates_project_card_status_proposal_to_disetujui(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Mobile Tourism App',
            'status_proposal' => 'pending',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id); // Dosen Pengampu

        ProjectDosenPenilai::create([
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPenilai->id,
            'assigned_by' => $this->admin->id,
        ]);

        $card->syncVerifikasiDosen();

        // 1. Dosen Pengampu ACC
        $res1 = $this->actingAs($this->dosenPengampu)
            ->postJson(route('project.verifikasi.acc', ['card' => $card->id]), [
                'status' => 'acc',
            ]);
        $res1->assertOk();
        $card->refresh();
        // Masih pending karena Dosen Penilai belum ACC
        $this->assertEquals('pending', $card->status_proposal);

        // 2. Dosen Penilai ACC
        $res2 = $this->actingAs($this->dosenPenilai)
            ->postJson(route('project.verifikasi.acc', ['card' => $card->id]), [
                'status' => 'acc',
            ]);
        $res2->assertOk();
        $card->refresh();
        // Sekarang semua dosen telah ACC -> disetujui!
        $this->assertEquals('disetujui', $card->status_proposal);
    }

    public function test_dosen_revisi_updates_project_card_status_proposal_to_revisi(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Machine Learning Classifier',
            'status_proposal' => 'pending',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);
        $card->syncVerifikasiDosen();

        $response = $this->actingAs($this->dosenPengampu)
            ->postJson(route('project.verifikasi.acc', ['card' => $card->id]), [
                'status' => 'revisi',
                'catatan' => 'Metodologi di Bab 3 belum lengkap, tolong perbaiki.',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => 'revisi',
            'status_proposal' => 'revisi',
        ]);

        $card->refresh();
        $this->assertEquals('revisi', $card->status_proposal);
    }

    public function test_get_status_endpoint_returns_involved_dosen_and_metrics(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Analytics Dashboard',
            'status_proposal' => 'pending',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);

        $response = $this->actingAs($this->dosenPengampu)
            ->getJson(route('project.verifikasi.status', ['card' => $card->id]));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'card' => ['id', 'title', 'status_proposal'],
            'involved_dosen',
            'stats' => ['total', 'acc', 'revisi', 'pending', 'percent', 'is_all_acc'],
            'can_acc',
        ]);
        $response->assertJson([
            'success' => true,
            'can_acc' => true,
        ]);
    }

    public function test_admin_can_acc_proposal_directly(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Autonomous Drone',
            'status_proposal' => 'pending',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);
        $card->syncVerifikasiDosen();

        $response = $this->actingAs($this->admin)
            ->postJson(route('project.verifikasi.acc', ['card' => $card->id]), [
                'status' => 'acc',
                'catatan' => 'ACC langsung oleh Admin PBL',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => 'acc',
        ]);
    }

    public function test_admin_and_evaluator_show_project_includes_involved_dosen(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Virtual Reality Classroom',
            'status_proposal' => 'pending',
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
            'can_acc',
            'is_admin',
            'available_dosen',
        ]);
        $this->assertTrue($resAdmin->json('is_admin'));
        $this->assertTrue($resAdmin->json('can_acc'));

        // Evaluator show project
        $resEval = $this->actingAs($this->dosenPengampu)
            ->getJson(route('evaluator.evaluasi.project.show', ['project' => $card->id]));

        $resEval->assertOk();
        $resEval->assertJsonStructure([
            'success',
            'project',
            'involved_dosen',
            'can_acc',
            'is_admin',
        ]);
        $this->assertTrue($resEval->json('can_acc'));
    }

    public function test_mahasiswa_proyek_index_includes_verifikasi_stats_and_dosen(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart Water Quality',
            'status_proposal' => 'pending',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach($this->mataKuliah->id);
        $card->syncVerifikasiDosen();

        $response = $this->actingAs($this->studentUser)
            ->get(route('proyek.index'));

        $response->assertOk();
        $response->assertSee('Proyek Smart Water Quality');
        $response->assertSee('Menunggu ACC Proposal');
    }

    public function test_admin_can_assign_dosen_as_pembimbing(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Autonomous Drone',
            'status_proposal' => 'pending',
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

        $this->assertDatabaseHas('project_verifikasi_dosen', [
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenLain->id,
        ]);
    }

    public function test_admin_can_remove_dosen_from_pembimbing(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart Greenhouse',
            'dosen_penentu_id' => $this->dosenLain->id,
            'status_proposal' => 'pending',
            'created_by' => $this->studentUser->id,
        ]);
        $card->syncVerifikasiDosen();

        $response = $this->actingAs($this->admin)
            ->postJson(route('project.dosen.remove', ['card' => $card->id]), [
                'dosen_id' => $this->dosenLain->id,
                'peran_tipe' => 'pembimbing',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $card->refresh();
        $this->assertNull($card->dosen_penentu_id);
        $this->assertDatabaseMissing('project_verifikasi_dosen', [
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenLain->id,
        ]);
    }

    public function test_admin_can_assign_and_remove_penilai_via_unified_endpoints(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart Aquaculture',
            'status_proposal' => 'pending',
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
            'status_proposal' => 'pending',
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
        $this->assertDatabaseHas('project_verifikasi_dosen', [
            'project_card_id' => $card->id,
            'dosen_id' => $this->dosenPengampu->id,
        ]);
    }
}
