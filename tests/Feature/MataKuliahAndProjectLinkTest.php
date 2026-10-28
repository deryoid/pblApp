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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MataKuliahAndProjectLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $dosen;

    protected User $studentUser;

    protected Mahasiswa $mahasiswa;

    protected Kelompok $kelompok;

    protected Periode $periode;

    protected Kelas $kelas;

    protected ProjectList $list;

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
            'nama_user' => 'Super Admin',
            'username' => 'admin_test',
            'email' => 'admin@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_hp' => '081234567891',
        ]);

        $this->dosen = User::create([
            'nama_user' => 'Dr. Fauzi Dosen',
            'username' => 'fauzi_test',
            'email' => 'fauzi@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '081234567892',
        ]);

        $this->studentUser = User::create([
            'nama_user' => 'Budi Santoso',
            'username' => 'budi_test',
            'email' => 'budi@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567893',
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $this->studentUser->id,
            'nim' => '202604001',
            'nama_mahasiswa' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'semester' => 4,
            'ipk' => 3.75,
            'status' => 'Aktif',
            'is_profile_complete' => true,
        ]);

        $this->kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 4 TRKJ',
            'nomor_kelompok' => 4,
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
            'name' => 'To Do',
            'position' => 0,
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
        ]);
    }

    public function test_admin_can_view_mata_kuliah_index_and_filter_by_semester(): void
    {
        $mk4 = MataKuliah::create([
            'kode_matkul' => 'MK401',
            'nama_matkul' => 'Keamanan Jaringan',
            'semester' => 4,
            'sks' => 3,
            'dosen_id' => $this->dosen->id,
            'is_active' => true,
        ]);

        $mk2 = MataKuliah::create([
            'kode_matkul' => 'MK201',
            'nama_matkul' => 'Struktur Data',
            'semester' => 2,
            'sks' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('matakuliah.index'));
        $response->assertStatus(200);
        $response->assertSee('Keamanan Jaringan');
        $response->assertSee('Struktur Data');
        $response->assertSee('Dr. Fauzi Dosen');

        // Filter semester 4
        $filterResponse = $this->actingAs($this->admin)->get(route('matakuliah.index', ['semester' => 4]));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Keamanan Jaringan');
        $filterResponse->assertDontSee('Struktur Data');
    }

    public function test_admin_can_create_mata_kuliah_with_dosen_pengampu(): void
    {
        $response = $this->actingAs($this->admin)->post(route('matakuliah.store'), [
            'kode_matkul' => 'MK402',
            'nama_matkul' => 'Cloud Computing',
            'semester' => 4,
            'sks' => 3,
            'dosen_id' => $this->dosen->id,
            'deskripsi' => 'Pengenalan teknologi komputasi awan',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('matakuliah.index', ['semester' => 4]));

        $this->assertDatabaseHas('mata_kuliah', [
            'kode_matkul' => 'MK402',
            'nama_matkul' => 'Cloud Computing',
            'semester' => 4,
            'sks' => 3,
            'dosen_id' => $this->dosen->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_mata_kuliah(): void
    {
        $mk = MataKuliah::create([
            'kode_matkul' => 'MK403',
            'nama_matkul' => 'Sistem Terdistribusi',
            'semester' => 4,
            'sks' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('matakuliah.update', $mk), [
            'kode_matkul' => 'MK403-REV',
            'nama_matkul' => 'Sistem Terdistribusi Lanjut',
            'semester' => 5,
            'sks' => 3,
            'dosen_id' => $this->dosen->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('matakuliah.index', ['semester' => 5]));

        $this->assertDatabaseHas('mata_kuliah', [
            'id' => $mk->id,
            'kode_matkul' => 'MK403-REV',
            'nama_matkul' => 'Sistem Terdistribusi Lanjut',
            'semester' => 5,
            'sks' => 3,
            'dosen_id' => $this->dosen->id,
        ]);
    }

    public function test_student_can_create_project_card_with_linked_mata_kuliah(): void
    {
        $mk1 = MataKuliah::create([
            'kode_matkul' => 'MK401',
            'nama_matkul' => 'Keamanan Jaringan',
            'semester' => 4,
            'sks' => 3,
            'dosen_id' => $this->dosen->id,
            'is_active' => true,
        ]);

        $proposal = UploadedFile::fake()->create('proposal_internal.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->studentUser)->post(route('proyek.cards.store'), [
            'title' => 'Sistem Firewall Kampus',
            'list' => $this->list->uuid,
            'skema_pbl' => 'Internal',
            'proposal_file' => $proposal,
            'mata_kuliah_ids' => [$mk1->id],
        ]);

        $response->assertSessionHasNoErrors();

        $card = ProjectCard::where('title', 'Sistem Firewall Kampus')->first();
        $this->assertNotNull($card);
        $this->assertTrue($card->mataKuliahs->contains($mk1->id));
        $this->assertSame($this->dosen->id, $card->mataKuliahs->first()->dosen_id);
    }

    public function test_student_can_update_project_card_mata_kuliah_relations(): void
    {
        $mk1 = MataKuliah::create([
            'kode_matkul' => 'MK401',
            'nama_matkul' => 'Keamanan Jaringan',
            'semester' => 4,
            'sks' => 3,
            'is_active' => true,
        ]);

        $mk2 = MataKuliah::create([
            'kode_matkul' => 'MK601',
            'nama_matkul' => 'Internet of Things',
            'semester' => 6,
            'sks' => 3,
            'is_active' => true,
        ]);

        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Proyek Smart Greenhouse',
            'skema_pbl' => 'External',
            'status_proyek' => 'Proses',
            'created_by' => $this->studentUser->id,
        ]);

        $card->mataKuliahs()->attach([$mk1->id]);

        // Update skema to External and select MK from semester 6 (free across semesters)
        $response = $this->actingAs($this->studentUser)->put(route('proyek.cards.update', $card), [
            'title' => 'Proyek Smart Greenhouse Terintegrasi',
            'skema_pbl' => 'External',
            'mata_kuliah_ids' => [$mk1->id, $mk2->id],
        ]);

        $response->assertSessionHasNoErrors();

        $card->refresh();
        $this->assertCount(2, $card->mataKuliahs);
        $this->assertTrue($card->mataKuliahs->contains($mk1->id));
        $this->assertTrue($card->mataKuliahs->contains($mk2->id));
    }
}
