<?php

namespace Tests\Feature;

use App\Models\AktivitasCard;
use App\Models\AktivitasList;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEvaluatorAndCrudOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $dosenPenilai;

    protected User $dosenLain;

    protected User $mahasiswaUser;

    protected Periode $periode;

    protected Kelompok $kelompok;

    protected ProjectList $projectList;

    protected ProjectCard $projectCard;

    protected AktivitasList $aktivitasList;

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

        $this->dosenPenilai = User::create([
            'nama_user' => 'Dr. Penilai Baru, M.Kom',
            'username' => '198501012010011005',
            'email' => 'penilai_baru@politala.ac.id',
            'password' => Hash::make('123456'),
            'role' => 'dosen',
            'no_hp' => '081234567891',
        ]);

        $this->dosenLain = User::create([
            'nama_user' => 'Ir. Dosen Luar, M.T',
            'username' => '198702022012021006',
            'email' => 'luar@politala.ac.id',
            'password' => Hash::make('123456'),
            'role' => 'dosen',
            'no_hp' => '081234567892',
        ]);

        $this->mahasiswaUser = User::create([
            'nama_user' => 'Budi Mahasiswa',
            'username' => '2401402099',
            'email' => 'budi@politala.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567899',
        ]);

        $kelas = Kelas::create([
            'kelas' => 'TRKJ 3A',
        ]);

        $mhs = Mahasiswa::create([
            'user_id' => $this->mahasiswaUser->id,
            'nama_mahasiswa' => 'Budi Mahasiswa',
            'nim' => '2401402099',
            'kelas_id' => $kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        $this->periode = Periode::create([
            'nama_periode' => 'Semester Ganjil 2025/2026',
            'periode' => '2025/2026 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 10 Alpha',
            'nomor_kelompok' => '10',
            'periode_id' => $this->periode->id,
            'kelas_id' => $kelas->id,
            'semester' => 3,
            'skema_kategori' => 'Internal',
        ]);

        $this->kelompok->mahasiswas()->attach($mhs->id, ['periode_id' => $this->periode->id, 'role' => 'Ketua']);

        $this->projectList = ProjectList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Perencanaan',
            'position' => 0,
        ]);

        $this->projectCard = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->projectList->id,
            'title' => 'Sistem Rekomendasi IoT',
            'description' => 'Implementasi sistem rekomendasi cerdas',
            'skema_pbl' => 'Internal',
            'status_proyek' => 'Proses',
            'status' => 'Proses',
            'progress' => 30,
            'position' => 0,
            'created_by' => $this->mahasiswaUser->id,
        ]);

        $this->aktivitasList = AktivitasList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Minggu 1',
            'rentang_tanggal' => '01 - 07 Oktober 2025',
            'status_evaluasi' => 'Belum Evaluasi',
            'position' => 0,
        ]);
    }

    public function test_admin_can_assign_dosen_or_admin_as_penilai_and_pembimbing(): void
    {
        // 1. Assign Dosen sebagai Penilai
        $response = $this->actingAs($this->admin)->postJson(route('project.dosen.assign', ['card' => $this->projectCard->uuid]), [
            'dosen_id' => $this->dosenPenilai->id,
            'peran' => 'penilai',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('project_dosen_penilai', [
            'project_card_id' => $this->projectCard->id,
            'dosen_id' => $this->dosenPenilai->id,
        ]);

        // 2. Assign Dosen sebagai Pembimbing
        $response2 = $this->actingAs($this->admin)->postJson(route('project.dosen.assign', ['card' => $this->projectCard->uuid]), [
            'dosen_id' => $this->dosenPenilai->id,
            'peran' => 'pembimbing',
        ]);

        $response2->assertOk();
        $this->assertEquals($this->dosenPenilai->id, $this->projectCard->fresh()->dosen_penentu_id);
    }

    public function test_assigned_dosen_gains_immediate_authorization_to_assess(): void
    {
        // Sebelum ditugaskan, dosenPenilai tidak berwenang
        $this->assertFalse($this->projectCard->canBeAssessedBy($this->dosenPenilai));

        // Admin menugaskan dosenPenilai
        $this->actingAs($this->admin)->postJson(route('project.dosen.assign', ['card' => $this->projectCard->uuid]), [
            'dosen_id' => $this->dosenPenilai->id,
            'peran' => 'penilai',
        ]);

        // Sekarang dosenPenilai berwenang menilai
        $freshCard = $this->projectCard->fresh();
        $this->assertTrue($freshCard->canBeAssessedBy($this->dosenPenilai));

        // Dosen yang belum ditugaskan tetap TIDAK berwenang
        $this->assertFalse($freshCard->canBeAssessedBy($this->dosenLain));
    }

    public function test_admin_can_remove_dosen_assignment_from_project_card(): void
    {
        // Tugaskan pembimbing & penilai
        $this->projectCard->update(['dosen_penentu_id' => $this->dosenPenilai->id]);

        $response = $this->actingAs($this->admin)->postJson(route('project.dosen.remove', ['card' => $this->projectCard->uuid]), [
            'dosen_id' => $this->dosenPenilai->id,
            'peran_tipe' => 'pembimbing',
        ]);

        $response->assertOk();
        $this->assertNull($this->projectCard->fresh()->dosen_penentu_id);
    }

    public function test_admin_can_add_project_card_for_kelompok(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.evaluasi.project.store', ['kelompok' => $this->kelompok->uuid]), [
            'title' => 'Proyek Baru Ditambahkan Admin',
            'description' => 'Deskripsi proyek dari admin',
            'skema_pbl' => 'External',
            'status_proyek' => 'Proses',
            'dosen_penentu_id' => $this->dosenPenilai->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('project_cards', [
            'kelompok_id' => $this->kelompok->id,
            'title' => 'Proyek Baru Ditambahkan Admin',
            'dosen_penentu_id' => $this->dosenPenilai->id,
        ]);
    }

    public function test_admin_and_authorized_dosen_can_update_project_card(): void
    {
        // Admin update
        $resAdmin = $this->actingAs($this->admin)->postJson(route('admin.evaluasi.project.update', ['card' => $this->projectCard->uuid]), [
            'title' => 'Judul Diperbarui Admin',
            'progress' => 50,
            'status_proyek' => 'Proses',
        ]);
        $resAdmin->assertOk();
        $this->assertEquals('Judul Diperbarui Admin', $this->projectCard->fresh()->title);

        // Dosen yang berwenang (tugaskan dulu)
        $this->projectCard->update(['dosen_penentu_id' => $this->dosenPenilai->id]);

        $resDosen = $this->actingAs($this->dosenPenilai)->putJson(route('dosen.evaluasi.project.update', ['card' => $this->projectCard->uuid]), [
            'title' => 'Judul Diperbarui Dosen Pembimbing',
            'progress' => 75,
            'status_proyek' => 'Proses',
        ]);
        $resDosen->assertOk();
        $this->assertEquals('Judul Diperbarui Dosen Pembimbing', $this->projectCard->fresh()->title);

        // Dosen lain yang tidak terlibat ditolak (403)
        $resDosenLain = $this->actingAs($this->dosenLain)->putJson(route('dosen.evaluasi.project.update', ['card' => $this->projectCard->uuid]), [
            'title' => 'Upaya Update Ilegal',
        ]);
        $resDosenLain->assertStatus(403);
    }

    public function test_mahasiswa_can_add_edit_and_delete_project_card(): void
    {
        // 1. Tambah kartu proyek oleh mahasiswa
        $resStore = $this->actingAs($this->mahasiswaUser)->post(route('proyek.cards.store'), [
            'title' => 'Kartu Tambahan Mahasiswa',
            'skema_pbl' => 'External',
            'status_proyek' => 'Proses',
        ]);
        $resStore->assertRedirect();
        $this->assertDatabaseHas('project_cards', [
            'kelompok_id' => $this->kelompok->id,
            'title' => 'Kartu Tambahan Mahasiswa',
        ]);

        $card = ProjectCard::where('title', 'Kartu Tambahan Mahasiswa')->first();

        // 2. Edit kartu proyek
        $resUpdate = $this->actingAs($this->mahasiswaUser)->put(route('proyek.cards.update', ['card' => $card->uuid]), [
            'title' => 'Kartu Tambahan Mahasiswa (Revisi)',
            'progress' => 40,
            'skema_pbl' => 'External',
            'status_proyek' => 'Proses',
        ]);
        $resUpdate->assertRedirect();
        $this->assertEquals('Kartu Tambahan Mahasiswa (Revisi)', $card->fresh()->title);

        // 3. Hapus kartu proyek
        $resDestroy = $this->actingAs($this->mahasiswaUser)->delete(route('proyek.cards.destroy', ['card' => $card->uuid]));
        $resDestroy->assertRedirect();
        $this->assertDatabaseMissing('project_cards', ['id' => $card->id]);
    }

    public function test_mahasiswa_deleting_project_list_cascades_cards(): void
    {
        $list = ProjectList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Kolom Uji Hapus',
            'position' => 1,
        ]);

        $cardInList = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $list->id,
            'title' => 'Kartu Dalam Kolom Uji',
            'skema_pbl' => 'External',
            'status_proyek' => 'Proses',
            'created_by' => $this->mahasiswaUser->id,
        ]);

        $resDelete = $this->actingAs($this->mahasiswaUser)->delete(route('proyek.lists.destroy', ['list' => $list->uuid]), ['cascade' => 1]);
        $resDelete->assertRedirect();

        $this->assertDatabaseMissing('project_lists', ['id' => $list->id]);
        $this->assertDatabaseMissing('project_cards', ['id' => $cardInList->id]);
    }

    public function test_mahasiswa_can_add_edit_with_bukti_link_and_delete_aktivitas_card(): void
    {
        // 1. Tambah aktivitas dengan bukti link
        $resStore = $this->actingAs($this->mahasiswaUser)->post(route('aktivitas.cards.store'), [
            'list' => $this->aktivitasList->uuid,
            'tanggal_aktivitas' => '2025-10-02',
            'description' => 'Membuat diagram arsitektur sistem IoT',
            'bukti_kegiatan' => 'drive.google.com/file/d/12345/view',
        ]);
        $resStore->assertRedirect();

        $actCard = AktivitasCard::where('list_aktivitas_id', $this->aktivitasList->id)->first();
        $this->assertNotNull($actCard);
        $this->assertEquals('https://drive.google.com/file/d/12345/view', $actCard->bukti_kegiatan);

        // 2. Edit aktivitas
        $resUpdate = $this->actingAs($this->mahasiswaUser)->put(route('aktivitas.cards.update', ['card' => $actCard->uuid]), [
            'tanggal_aktivitas' => '2025-10-03',
            'description' => 'Membuat diagram arsitektur sistem IoT (Selesai)',
            'bukti_kegiatan' => 'https://drive.google.com/file/d/67890/view',
        ]);
        $resUpdate->assertRedirect();
        $this->assertEquals('Membuat diagram arsitektur sistem IoT (Selesai)', $actCard->fresh()->description);
        $this->assertEquals('https://drive.google.com/file/d/67890/view', $actCard->fresh()->bukti_kegiatan);

        // 3. Hapus aktivitas
        $resDestroy = $this->actingAs($this->mahasiswaUser)->delete(route('aktivitas.cards.destroy', ['card' => $actCard->uuid]));
        $resDestroy->assertRedirect();
        $this->assertDatabaseMissing('aktivitas_cards', ['id' => $actCard->id]);
    }

    public function test_mahasiswa_deleting_aktivitas_list_cascades_cards(): void
    {
        $list = AktivitasList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Minggu Uji Hapus',
            'position' => 2,
        ]);

        $actInList = AktivitasCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_aktivitas_id' => $list->id,
            'description' => 'Aktivitas yang akan ikut terhapus',
            'created_by' => $this->mahasiswaUser->id,
        ]);

        $resDelete = $this->actingAs($this->mahasiswaUser)->delete(route('aktivitas.lists.destroy', ['list' => $list->uuid]), ['cascade' => 1]);
        $resDelete->assertRedirect();

        $this->assertDatabaseMissing('aktivitas_lists', ['id' => $list->id]);
        $this->assertDatabaseMissing('aktivitas_cards', ['id' => $actInList->id]);
    }

    public function test_admin_can_toggle_aktivitas_list_status(): void
    {
        // Ubah menjadi Sudah Evaluasi
        $response = $this->actingAs($this->admin)->postJson(route('admin.evaluasi.aktivitas.status', ['list' => $this->aktivitasList->uuid]), [
            'status' => 'Sudah Evaluasi',
        ]);

        $response->assertOk();
        $this->assertEquals('Sudah Evaluasi', $this->aktivitasList->fresh()->status_evaluasi);

        // Ubah kembali menjadi Belum Evaluasi
        $response2 = $this->actingAs($this->admin)->postJson(route('admin.evaluasi.aktivitas.status', ['list' => $this->aktivitasList->uuid]), [
            'status' => 'Belum Evaluasi',
        ]);

        $response2->assertOk();
        $this->assertEquals('Belum Evaluasi', $this->aktivitasList->fresh()->status_evaluasi);
    }
}
