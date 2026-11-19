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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProyekMahasiswaTest extends TestCase
{
    use RefreshDatabase;

    protected Periode $periode;

    protected Kelas $kelas;

    protected User $studentUser;

    protected Mahasiswa $studentMhs;

    protected Kelompok $kelompok;

    protected ProjectList $list1;

    protected ProjectList $list2;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->periode = Periode::create([
            'periode' => '2026/2027 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->kelas = Kelas::create([
            'kelas' => 'TRPL 3A',
            'semester' => 3,
        ]);

        $this->studentUser = User::create([
            'nama_user' => 'Mahasiswa PBL 1',
            'username' => 'mhs_pbl_1',
            'email' => 'mhs1@politala.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567890',
        ]);

        $this->studentMhs = Mahasiswa::create([
            'user_id' => $this->studentUser->id,
            'nama_mahasiswa' => $this->studentUser->nama_user,
            'nim' => '230411100010',
            'kelas_id' => $this->kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        $this->kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 1 - Alpha Proyek',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode->id,
            'semester' => 3,
            'max_members' => 4,
            'status_bergabung' => 'terbuka',
        ]);

        $this->kelompok->mahasiswas()->attach($this->studentMhs->id, [
            'role' => 'Ketua',
            'periode_id' => $this->periode->id,
        ]);

        $this->list1 = ProjectList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'To Do / Rencana',
            'position' => 0,
        ]);

        $this->list2 = ProjectList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Sedang Dikerjakan',
            'position' => 1,
        ]);
    }

    public function test_student_without_group_can_view_project_page_with_warning(): void
    {
        $soloUser = User::create([
            'nama_user' => 'Mahasiswa Tanpa Kelompok',
            'username' => 'mhs_solo',
            'email' => 'solo@politala.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567891',
        ]);

        Mahasiswa::create([
            'user_id' => $soloUser->id,
            'nama_mahasiswa' => $soloUser->nama_user,
            'nim' => '230411100099',
            'kelas_id' => $this->kelas->id,
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        $response = $this->actingAs($soloUser)->get(route('proyek.index'));
        $response->assertStatus(200);
        $response->assertSee('Anda belum memiliki kelompok pada periode PBL');
    }

    public function test_student_with_group_can_view_project_page(): void
    {
        $response = $this->actingAs($this->studentUser)->get(route('proyek.index'));
        $response->assertStatus(200);
        $response->assertSee('Kelompok 1 - Alpha Proyek');
        $response->assertSee('To Do / Rencana');
        $response->assertSee('Sedang Dikerjakan');
    }

    public function test_student_can_create_internal_project_with_proposal(): void
    {
        $file = UploadedFile::fake()->create('proposal_proyek_alpha.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->studentUser)->post(route('proyek.cards.store'), [
            'title' => 'Sistem Informasi Manajemen Aset',
            'description' => 'Deskripsi luaran sistem aset',
            'list' => $this->list1->uuid,
            'skema_pbl' => 'Internal',
            'proposal_file' => $file,
            'status_proyek' => 'Proses',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_cards', [
            'title' => 'Sistem Informasi Manajemen Aset',
            'kelompok_id' => $this->kelompok->id,
            'list_id' => $this->list1->id,
            'skema_pbl' => 'Internal',
            'status_proposal' => 'pending',
        ]);

        $card = ProjectCard::where('title', 'Sistem Informasi Manajemen Aset')->first();
        $this->assertNotNull($card->proposal_file_path);
        Storage::disk('public')->assertExists($card->proposal_file_path);
    }

    public function test_student_can_create_internal_project_without_proposal(): void
    {
        $response = $this->actingAs($this->studentUser)->post(route('proyek.cards.store'), [
            'title' => 'Proyek Tanpa Proposal',
            'list' => $this->list1->uuid,
            'skema_pbl' => 'Internal',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('project_cards', [
            'title' => 'Proyek Tanpa Proposal',
        ]);
    }

    public function test_student_can_create_external_project_without_proposal(): void
    {
        $response = $this->actingAs($this->studentUser)->post(route('proyek.cards.store'), [
            'title' => 'Aplikasi Kasir Toko Mitra',
            'list' => $this->list1->uuid,
            'skema_pbl' => 'External',
            'nama_mitra' => 'CV Mitra Sejahtera',
            'kontak_mitra' => '081299887766',
            'link_drive_proyek' => 'drive.google.com/test-folder',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('project_cards', [
            'title' => 'Aplikasi Kasir Toko Mitra',
            'nama_mitra' => 'CV Mitra Sejahtera',
            'skema_pbl' => 'External',
            'jenis_proyek' => 'external',
            'link_drive_proyek' => 'https://drive.google.com/test-folder',
        ]);
    }

    public function test_student_can_update_project_card_and_sync_mata_kuliah(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list1->id,
            'title' => 'Proyek Awal',
            'skema_pbl' => 'External',
            'status_proyek' => 'Proses',
        ]);

        $mk1 = MataKuliah::create([
            'kode_matkul' => 'MK301',
            'nama_matkul' => 'Rekayasa Perangkat Lunak',
            'semester' => 3,
            'sks' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->studentUser)->put(route('proyek.cards.update', $card), [
            'title' => 'Proyek Awal Diperbarui',
            'skema_pbl' => 'External',
            'progress' => 50,
            'mata_kuliah_ids' => [$mk1->id],
        ]);

        $response->assertSessionHasNoErrors();
        $card->refresh();
        $this->assertSame('Proyek Awal Diperbarui', $card->title);
        $this->assertSame(50, $card->progress);
        $this->assertTrue($card->mataKuliahs->contains($mk1->id));
    }

    public function test_student_can_clear_mata_kuliah_from_card(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list1->id,
            'title' => 'Proyek dengan Matkul',
            'skema_pbl' => 'External',
        ]);

        $mk = MataKuliah::create([
            'kode_matkul' => 'MK302',
            'nama_matkul' => 'Basis Data',
            'semester' => 3,
            'sks' => 3,
            'is_active' => true,
        ]);
        $card->mataKuliahs()->attach([$mk->id]);
        $this->assertCount(1, $card->mataKuliahs);

        // Update tanpa mengirim mata_kuliah_ids (unselect all)
        $response = $this->actingAs($this->studentUser)->put(route('proyek.cards.update', $card), [
            'title' => 'Proyek dengan Matkul',
            'skema_pbl' => 'External',
        ]);

        $response->assertSessionHasNoErrors();
        $card->refresh();
        $this->assertCount(0, $card->mataKuliahs);
    }

    public function test_student_can_delete_project_card_and_file_is_removed(): void
    {
        $file = UploadedFile::fake()->create('proposal_delete_test.pdf', 200, 'application/pdf');
        $path = $file->store('proposals', 'public');

        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list1->id,
            'title' => 'Proyek Hendak Dihapus',
            'skema_pbl' => 'Internal',
            'proposal_file_path' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->studentUser)->delete(route('proyek.cards.destroy', $card));
        $response->assertRedirect();

        $this->assertDatabaseMissing('project_cards', ['id' => $card->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_student_can_reorder_card_between_lists(): void
    {
        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list1->id,
            'title' => 'Kartu Geser',
            'skema_pbl' => 'External',
            'position' => 0,
        ]);

        $response = $this->actingAs($this->studentUser)->postJson(route('proyek.reorder'), [
            'card_id' => $card->uuid,
            'to_list' => $this->list2->uuid,
            'position' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $card->refresh();
        $this->assertSame($this->list2->id, $card->list_id);
    }

    public function test_student_can_create_rename_and_reorder_lists(): void
    {
        // 1. Create list
        $responseCreate = $this->actingAs($this->studentUser)->post(route('proyek.lists.store'), [
            'name' => 'Kolom Baru 3',
        ]);
        $responseCreate->assertRedirect();
        $this->assertDatabaseHas('project_lists', [
            'kelompok_id' => $this->kelompok->id,
            'name' => 'Kolom Baru 3',
        ]);

        $newList = ProjectList::where('name', 'Kolom Baru 3')->first();

        // 2. Rename list
        $responseUpdate = $this->actingAs($this->studentUser)->put(route('proyek.lists.update', $newList), [
            'name' => 'Kolom Baru 3 Direvisi',
        ]);
        $responseUpdate->assertRedirect();
        $newList->refresh();
        $this->assertSame('Kolom Baru 3 Direvisi', $newList->name);

        // 3. Reorder lists
        $responseReorder = $this->actingAs($this->studentUser)->postJson(route('proyek.lists.reorder'), [
            'list_ids' => [$newList->uuid, $this->list1->uuid, $this->list2->uuid],
        ]);
        $responseReorder->assertStatus(200);
        $responseReorder->assertJson(['status' => 'ok']);

        $newList->refresh();
        $this->assertSame(0, $newList->position);
    }

    public function test_student_cannot_delete_list_containing_cards(): void
    {
        ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list1->id,
            'title' => 'Kartu dalam List 1',
            'skema_pbl' => 'External',
        ]);

        $response = $this->actingAs($this->studentUser)->delete(route('proyek.lists.destroy', $this->list1));
        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('project_lists', ['id' => $this->list1->id]);
    }

    public function test_student_can_delete_empty_list(): void
    {
        $response = $this->actingAs($this->studentUser)->delete(route('proyek.lists.destroy', $this->list2));
        $response->assertRedirect();
        $this->assertDatabaseMissing('project_lists', ['id' => $this->list2->id]);
    }

    public function test_student_cannot_modify_other_group_cards(): void
    {
        $otherKelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Lain',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode->id,
            'semester' => 3,
        ]);

        $otherList = ProjectList::create([
            'kelompok_id' => $otherKelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Other List',
        ]);

        $otherCard = ProjectCard::create([
            'kelompok_id' => $otherKelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $otherList->id,
            'title' => 'Kartu Kelompok Lain',
            'skema_pbl' => 'External',
        ]);

        $response = $this->actingAs($this->studentUser)->put(route('proyek.cards.update', $otherCard), [
            'title' => 'Hacked Title',
            'skema_pbl' => 'External',
        ]);

        $response->assertStatus(403);
        $otherCard->refresh();
        $this->assertSame('Kartu Kelompok Lain', $otherCard->title);
    }

    public function test_student_can_quick_setup_default_project_columns(): void
    {
        ProjectCard::query()->delete();
        ProjectList::query()->delete();

        $response = $this->actingAs($this->studentUser)->post(route('proyek.lists.quick-setup'));
        $response->assertRedirect();

        $this->assertDatabaseHas('project_lists', [
            'kelompok_id' => $this->kelompok->id,
            'name' => 'Perencanaan',
        ]);
        $this->assertDatabaseHas('project_lists', [
            'kelompok_id' => $this->kelompok->id,
            'name' => 'Pelaksanaan',
        ]);
        $this->assertDatabaseHas('project_lists', [
            'kelompok_id' => $this->kelompok->id,
            'name' => 'Selesai',
        ]);
    }

    public function test_student_can_create_project_even_when_group_has_no_columns_auto_creates_default_list(): void
    {
        ProjectCard::query()->delete();
        ProjectList::query()->delete();

        $response = $this->actingAs($this->studentUser)->post(route('proyek.cards.store'), [
            'title' => 'Sistem IoT Smart Campus',
            'skema_pbl' => 'External',
            'status_proyek' => 'Proses',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_lists', [
            'kelompok_id' => $this->kelompok->id,
            'name' => 'Perencanaan',
        ]);
        $this->assertDatabaseHas('project_cards', [
            'kelompok_id' => $this->kelompok->id,
            'title' => 'Sistem IoT Smart Campus',
        ]);
    }

    public function test_student_can_quick_setup_default_aktivitas_columns(): void
    {
        $response = $this->actingAs($this->studentUser)->post(route('aktivitas.lists.quick-setup'));
        $response->assertRedirect();

        $this->assertDatabaseHas('aktivitas_lists', [
            'kelompok_id' => $this->kelompok->id,
            'name' => 'Minggu 1',
        ]);
        $this->assertDatabaseHas('aktivitas_lists', [
            'kelompok_id' => $this->kelompok->id,
            'name' => 'Minggu 4',
        ]);
    }

    public function test_student_can_create_aktivitas_even_when_no_columns_auto_creates_default_list(): void
    {
        \App\Models\AktivitasCard::query()->delete();
        \App\Models\AktivitasList::query()->delete();

        $response = $this->actingAs($this->studentUser)->post(route('aktivitas.cards.store'), [
            'description' => 'Mengerjakan desain arsitektur sistem IoT',
            'tanggal_aktivitas' => '2026-11-08',
            'bukti_kegiatan' => 'https://drive.google.com/logbook',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aktivitas_lists', [
            'kelompok_id' => $this->kelompok->id,
            'name' => 'Minggu 1',
        ]);
        $this->assertDatabaseHas('aktivitas_cards', [
            'kelompok_id' => $this->kelompok->id,
            'description' => 'Mengerjakan desain arsitektur sistem IoT',
        ]);
    }

    public function test_student_can_delete_empty_list_even_when_active_period_differs(): void
    {
        // Simulasi periode aktif baru dibuat di sistem
        $newPeriod = Periode::create([
            'periode' => '2027/2028 Genap',
            'status_periode' => 'Aktif',
        ]);
        $this->periode->update(['status_periode' => 'Tidak Aktif']);

        $listToDelete = ProjectList::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Kolom Uji Hapus',
            'position' => 99,
        ]);

        $response = $this->actingAs($this->studentUser)->delete(route('proyek.lists.destroy', $listToDelete));
        $response->assertRedirect();
        $this->assertDatabaseMissing('project_lists', ['id' => $listToDelete->id]);
    }

    public function test_student_cannot_delete_other_group_list(): void
    {
        $otherKelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Lain 99',
            'skema_kategori' => 'Internal',
            'periode_id' => $this->periode->id,
            'semester' => 3,
        ]);

        $otherList = ProjectList::create([
            'kelompok_id' => $otherKelompok->id,
            'periode_id' => $this->periode->id,
            'name' => 'Kolom Milik Orang Lain',
            'position' => 0,
        ]);

        $response = $this->actingAs($this->studentUser)->delete(route('proyek.lists.destroy', $otherList));
        $response->assertStatus(403);
        $this->assertDatabaseHas('project_lists', ['id' => $otherList->id]);
    }

    public function test_student_can_delete_card_even_when_active_period_differs(): void
    {
        // Simulasi perubahan periode aktif
        Periode::create([
            'periode' => '2028/2029 Ganjil',
            'status_periode' => 'Aktif',
        ]);
        $this->periode->update(['status_periode' => 'Tidak Aktif']);

        $cardToDelete = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list1->id,
            'title' => 'Kartu Uji Hapus',
            'skema_pbl' => 'External',
        ]);

        $response = $this->actingAs($this->studentUser)->delete(route('proyek.cards.destroy', $cardToDelete));
        $response->assertRedirect();
        $this->assertDatabaseMissing('project_cards', ['id' => $cardToDelete->id]);
    }

    public function test_student_can_delete_all_projects_and_lists_until_completely_empty(): void
    {
        // Tambahkan kartu ke list 1 dan list 2
        $card1 = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list1->id,
            'title' => 'Project 1',
            'skema_pbl' => 'Internal',
        ]);
        $card2 = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list2->id,
            'title' => 'Project 2',
            'skema_pbl' => 'External',
        ]);

        // Hapus card 1
        $r1 = $this->actingAs($this->studentUser)->delete(route('proyek.cards.destroy', $card1));
        $r1->assertRedirect();
        $this->assertDatabaseMissing('project_cards', ['id' => $card1->id]);

        // Hapus list 1 (sekarang kosong)
        $rList1 = $this->actingAs($this->studentUser)->delete(route('proyek.lists.destroy', $this->list1));
        $rList1->assertRedirect();
        $this->assertDatabaseMissing('project_lists', ['id' => $this->list1->id]);

        // Hapus card 2 (kartu terakhir sampai habis)
        $r2 = $this->actingAs($this->studentUser)->delete(route('proyek.cards.destroy', $card2));
        $r2->assertRedirect();
        $this->assertDatabaseMissing('project_cards', ['id' => $card2->id]);

        // Hapus list 2 (kolom terakhir sampai habis)
        $rList2 = $this->actingAs($this->studentUser)->delete(route('proyek.lists.destroy', $this->list2));
        $rList2->assertRedirect();
        $this->assertDatabaseMissing('project_lists', ['id' => $this->list2->id]);

        // Pastikan halaman index tetap tampil normal tanpa error 403
        $indexResponse = $this->actingAs($this->studentUser)->get(route('proyek.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Belum ada kolom untuk kelompok ini.');
    }

    public function test_online_server_scenario_unlinked_student_creates_project_and_deletes_columns_until_empty(): void
    {
        $unlinkedUser = User::create([
            'nama_user' => 'Siswa Online',
            'username' => '230411199999',
            'email' => 'siswa.online@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '089999999999',
        ]);

        $mhsRecord = Mahasiswa::create([
            'nama_mahasiswa' => 'Siswa Online',
            'nim' => '230411199999',
            'kelas_id' => $this->kelas->id,
            'semester' => 3,
            'user_id' => null,
            'is_profile_complete' => true,
        ]);

        $newGroup = Kelompok::create([
            'nama_kelompok' => 'Kelompok Online Test',
            'skema_kategori' => 'External',
            'periode_id' => $this->periode->id,
        ]);

        $newGroup->mahasiswas()->attach($mhsRecord->id, [
            'periode_id' => $this->periode->id,
            'role' => 'Ketua',
        ]);

        // 1. Mahasiswa membuat proyek baru saat kelompok belum punya kolom
        $storeResponse = $this->actingAs($unlinkedUser)->post(route('proyek.cards.store'), [
            'title' => 'Proyek Online Perdana',
            'skema_pbl' => 'External',
        ]);
        $storeResponse->assertRedirect();

        // Kolom 'Perencanaan' otomatis dibuat
        $autoList = ProjectList::where('kelompok_id', $newGroup->id)->first();
        $this->assertNotNull($autoList);
        $this->assertSame('Perencanaan', $autoList->name);

        $createdCard = ProjectCard::where('kelompok_id', $newGroup->id)->first();
        $this->assertNotNull($createdCard);

        // 2. Mahasiswa menghapus kartu proyek
        $delCardResponse = $this->actingAs($unlinkedUser)->delete(route('proyek.cards.destroy', $createdCard));
        $delCardResponse->assertRedirect();
        $this->assertDatabaseMissing('project_cards', ['id' => $createdCard->id]);

        // 3. Mahasiswa menghapus kolom sampai habis
        $delListResponse = $this->actingAs($unlinkedUser)->delete(route('proyek.lists.destroy', $autoList));
        $delListResponse->assertRedirect();
        $this->assertDatabaseMissing('project_lists', ['id' => $autoList->id]);

        // 4. Pastikan tidak ada 403, dan auto-link user_id berhasil
        $mhsRecord->refresh();
        $this->assertSame($unlinkedUser->id, $mhsRecord->user_id);

        $indexResponse = $this->actingAs($unlinkedUser)->get(route('proyek.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Belum ada kolom untuk kelompok ini.');
    }

    public function test_student_can_edit_internal_project_even_if_no_proposal_was_initially_uploaded(): void
    {
        // Kartu internal yang belum punya file proposal (misal dibuat via seeder / draf)
        $card = ProjectCard::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'kelompok_id' => $this->kelompok->id,
            'list_id' => $this->list1->id,
            'periode_id' => $this->periode->id,
            'title' => 'Proyek Awal Tanpa Proposal',
            'skema_pbl' => 'Internal',
            'status_proyek' => 'Proses',
            'proposal_file_path' => null,
            'created_by' => $this->studentUser->id,
        ]);

        $response = $this->actingAs($this->studentUser)->put(route('proyek.cards.update', $card), [
            'title' => 'Judul Proyek Diperbarui',
            'description' => 'Deskripsi baru proyek',
            'skema_pbl' => 'Internal',
            'progress' => 50,
            'status_proyek' => 'Proses',
        ]);

        $response->assertRedirect();
        $card->refresh();
        $this->assertSame('Judul Proyek Diperbarui', $card->title);
        $this->assertSame(50, $card->progress);
        $this->assertSame('Deskripsi baru proyek', $card->description);
    }

    public function test_card_with_numeric_prefix_uuid_can_be_edited_and_deleted_without_type_juggling_403(): void
    {
        // 1. Buat user dan Card 1 untuk kelompok lain
        $otherUser = User::create([
            'nama_user' => 'User Lain',
            'username' => 'user_lain_99',
            'email' => 'user.lain99@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'no_hp' => '089999999988',
        ]);
        $otherGroup = Kelompok::create([
            'nama_kelompok' => 'Kelompok Lain 99',
            'skema_kategori' => 'External',
            'periode_id' => $this->periode->id,
        ]);
        $otherList = ProjectList::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'kelompok_id' => $otherGroup->id,
            'periode_id' => $this->periode->id,
            'name' => 'Backlog',
            'position' => 0,
        ]);
        $card1 = ProjectCard::create([
            'uuid' => '99999999-9999-9999-9999-999999999999',
            'kelompok_id' => $otherGroup->id,
            'list_id' => $otherList->id,
            'periode_id' => $this->periode->id,
            'title' => 'Kartu Milik Kelompok Lain (ID 1)',
            'created_by' => $otherUser->id,
        ]);

        // 2. Buat Card milik kelompok user saat ini dengan UUID berawalan digit sama dengan ID card1 (misal awalan $card1->id)
        $specialUuid = $card1->id.'abcdef12-3456-7890-abcd-ef1234567890';
        $myCard = ProjectCard::create([
            'uuid' => $specialUuid,
            'kelompok_id' => $this->kelompok->id,
            'list_id' => $this->list1->id,
            'periode_id' => $this->periode->id,
            'title' => 'Kartu Saya dengan UUID Berawalan Angka',
            'created_by' => $this->studentUser->id,
        ]);

        // Update kartu saya: harus berhasil mengupdate myCard, bukan 403 karena tertukar ke card1
        $updateRes = $this->actingAs($this->studentUser)->put(route('proyek.cards.update', $myCard), [
            'title' => 'Judul Kartu Saya Berhasil Diubah',
            'skema_pbl' => 'External',
        ]);
        $updateRes->assertRedirect();
        $myCard->refresh();
        $this->assertSame('Judul Kartu Saya Berhasil Diubah', $myCard->title);
        $card1->refresh();
        $this->assertSame('Kartu Milik Kelompok Lain (ID 1)', $card1->title);

        // Hapus kartu saya: harus menghapus myCard, bukan card1
        $delRes = $this->actingAs($this->studentUser)->delete(route('proyek.cards.destroy', $myCard));
        $delRes->assertRedirect();
        $this->assertDatabaseMissing('project_cards', ['uuid' => $specialUuid]);
        $this->assertDatabaseHas('project_cards', ['id' => $card1->id]);
    }

    public function test_unassigned_cards_auto_heal_and_display_on_kanban_board(): void
    {
        // Buat card tanpa list (misal hasil seeder)
        $orphanCard = ProjectCard::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'kelompok_id' => $this->kelompok->id,
            'list_id' => null, // Belum punya list
            'periode_id' => $this->periode->id,
            'title' => 'Kartu Tanpa List Awal',
            'status_proyek' => 'Proses',
            'created_by' => $this->studentUser->id,
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('proyek.index'));
        $response->assertStatus(200);
        $response->assertSee('Kartu Tanpa List Awal');

        // Pastikan card sekarang sudah ditautkan ke list
        $orphanCard->refresh();
        $this->assertNotNull($orphanCard->list_id);
    }

    public function test_project_card_renders_clear_visual_hierarchy_and_elements(): void
    {
        $card = ProjectCard::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'kelompok_id' => $this->kelompok->id,
            'list_id' => $this->list1->id,
            'periode_id' => $this->periode->id,
            'title' => 'Sistem Presensi RFID Kampus',
            'skema_pbl' => 'Teaching Factory',
            'nama_mitra' => 'PT Solusi Teknologi Bersama',
            'kontak_mitra' => '081234567890',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonths(2)->toDateString(),
            'progress' => 65,
            'status_proyek' => 'Proses',
            'created_by' => $this->studentUser->id,
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('proyek.index'));
        $response->assertStatus(200);

        // Verifikasi elemen hierarki baru kartu proyek
        $response->assertSee('card-title-hero');
        $response->assertSee('Sistem Presensi RFID Kampus');
        $response->assertSee('card-skema-pill');
        $response->assertSee('Teaching Factory');
        $response->assertSee('PT Solusi Teknologi Bersama');
        $response->assertSee('card-progress-box');
        $response->assertSee('65%');
        $response->assertSee('card-actions-toolbar');
    }
}
