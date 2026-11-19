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

    protected User $dosen2;

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

        $this->dosen2 = User::create([
            'nama_user' => 'Nurul Hidayati, M.Cs.',
            'username' => 'nurul_test',
            'email' => 'nurul@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '081234567894',
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
            'is_active' => true,
        ]);
        $mk4->dosenPengampu()->attach([$this->dosen->id, $this->dosen2->id]);

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
        $response->assertSee('Nurul Hidayati, M.Cs.');
        $response->assertSee('Team Teaching (2 Dosen)');

        // Filter semester 4
        $filterResponse = $this->actingAs($this->admin)->get(route('matakuliah.index', ['semester' => 4]));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Keamanan Jaringan');
        $filterResponse->assertDontSee('Struktur Data');
    }

    public function test_mata_kuliah_can_have_two_dosen_and_dosen_can_teach_two_or_more_courses(): void
    {
        $mk1 = MataKuliah::create([
            'kode_matkul' => 'MK401',
            'nama_matkul' => 'Algoritma & Pemrograman',
            'semester' => 1,
            'sks' => 3,
            'is_active' => true,
        ]);

        $mk2 = MataKuliah::create([
            'kode_matkul' => 'MK402',
            'nama_matkul' => 'Pemrograman Web Lanjut',
            'semester' => 3,
            'sks' => 3,
            'is_active' => true,
        ]);

        // 1 mata kuliah (MK1) memiliki 2 dosen pengampu (Dr. Fauzi & Nurul)
        $mk1->dosenPengampu()->sync([$this->dosen->id, $this->dosen2->id]);

        // Dosen Dr. Fauzi juga mengampu mata kuliah yang berbeda (MK2)
        $mk2->dosenPengampu()->sync([$this->dosen->id]);

        $mk1->refresh();
        $mk2->refresh();
        $this->dosen->refresh();

        // Verifikasi 1 mata kuliah diampu 2 dosen
        $this->assertCount(2, $mk1->dosenPengampu);
        $this->assertTrue($mk1->dosenPengampu->contains($this->dosen->id));
        $this->assertTrue($mk1->dosenPengampu->contains($this->dosen2->id));

        // Verifikasi 1 dosen dapat mengampu 2 atau lebih mata kuliah berbeda
        $this->assertCount(2, $this->dosen->mataKuliahsPengampu);
        $this->assertTrue($this->dosen->mataKuliahsPengampu->contains($mk1->id));
        $this->assertTrue($this->dosen->mataKuliahsPengampu->contains($mk2->id));
    }

    public function test_admin_can_create_mata_kuliah_with_multiple_dosen_pengampu(): void
    {
        $response = $this->actingAs($this->admin)->post(route('matakuliah.store'), [
            'kode_matkul' => 'MK402',
            'nama_matkul' => 'Cloud Computing',
            'semester' => 4,
            'sks' => 3,
            'dosen_ids' => [$this->dosen->id, $this->dosen2->id],
            'deskripsi' => 'Pengenalan teknologi komputasi awan',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('matakuliah.index', ['semester' => 4]));

        $mk = MataKuliah::where('kode_matkul', 'MK402')->first();
        $this->assertNotNull($mk);
        $this->assertCount(2, $mk->dosenPengampu);
        $this->assertTrue($mk->dosenPengampu->contains($this->dosen->id));
        $this->assertTrue($mk->dosenPengampu->contains($this->dosen2->id));
    }

    public function test_admin_can_access_edit_mata_kuliah_page(): void
    {
        $mk = MataKuliah::create([
            'kode_matkul' => 'MK402',
            'nama_matkul' => 'Jaringan Komputer Lanjut',
            'semester' => 4,
            'sks' => 3,
            'is_active' => true,
        ]);
        $mk->dosenPengampu()->sync([$this->dosen->id]);

        $response = $this->actingAs($this->admin)->get(route('matakuliah.edit', $mk));

        $response->assertOk();
        $response->assertViewIs('admin.matakuliah.edit');
        $response->assertViewHas('mataKuliah');
        $response->assertSee('Jaringan Komputer Lanjut');
        $response->assertSee($this->dosen->nama_user);
    }

    public function test_admin_can_update_mata_kuliah_dosen_pengampu(): void
    {
        $mk = MataKuliah::create([
            'kode_matkul' => 'MK403',
            'nama_matkul' => 'Sistem Terdistribusi',
            'semester' => 4,
            'sks' => 2,
            'is_active' => true,
        ]);
        $mk->dosenPengampu()->sync([$this->dosen->id]);

        $response = $this->actingAs($this->admin)->put(route('matakuliah.update', $mk), [
            'kode_matkul' => 'MK403-REV',
            'nama_matkul' => 'Sistem Terdistribusi Lanjut',
            'semester' => 5,
            'sks' => 3,
            'dosen_ids' => [$this->dosen->id, $this->dosen2->id],
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('matakuliah.index', ['semester' => 5]));

        $mk->refresh();
        $this->assertSame('MK403-REV', $mk->kode_matkul);
        $this->assertCount(2, $mk->dosenPengampu);
        $this->assertTrue($mk->dosenPengampu->contains($this->dosen->id));
        $this->assertTrue($mk->dosenPengampu->contains($this->dosen2->id));
    }

    public function test_student_can_create_project_card_with_linked_mata_kuliah(): void
    {
        $mk1 = MataKuliah::create([
            'kode_matkul' => 'MK401',
            'nama_matkul' => 'Keamanan Jaringan',
            'semester' => 4,
            'sks' => 3,
            'is_active' => true,
        ]);
        $mk1->dosenPengampu()->attach([$this->dosen->id, $this->dosen2->id]);

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
        $this->assertCount(2, $card->mataKuliahs->first()->dosenPengampu);
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
        $mk1->dosenPengampu()->attach([$this->dosen->id]);

        $mk2 = MataKuliah::create([
            'kode_matkul' => 'MK601',
            'nama_matkul' => 'Internet of Things',
            'semester' => 6,
            'sks' => 3,
            'is_active' => true,
        ]);
        $mk2->dosenPengampu()->attach([$this->dosen->id, $this->dosen2->id]);

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

    public function test_evaluasi_detail_renders_all_related_mata_kuliah_and_evaluators_matrix(): void
    {
        $mk = MataKuliah::create([
            'kode_matkul' => 'MK405',
            'nama_matkul' => 'Rekayasa Perangkat Lunak Lanjut',
            'semester' => 4,
            'sks' => 4,
            'is_active' => true,
        ]);
        $mk->dosenPengampu()->attach([$this->dosen->id]);

        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Sistem Rekam Medis Digital',
            'skema_pbl' => 'External',
            'nama_mitra' => 'RSUD Daerah',
            'kontak_mitra' => '081299887766',
            'status_proyek' => 'Proses',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach([$mk->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.evaluasi.kelompok.show', $this->kelompok));
        $response->assertStatus(200);

        // Verifikasi section informasi mata kuliah terkait & tim evaluator
        $response->assertSee('Informasi Mata Kuliah Terkait');
        $response->assertSee('Tim Evaluator');
        $response->assertSee('MK405');
        $response->assertSee('Rekayasa Perangkat Lunak Lanjut');
        $response->assertSee('4 SKS');
        $response->assertSee($this->dosen->nama_user);
        $response->assertSee('Dosen Pengampu');

        // Verifikasi wewenang evaluator
        $response->assertSee('Siapa Saja yang Bisa Mengevaluasi');
        $response->assertSee('Mitra Industri');
        $response->assertSee('RSUD Daerah');
    }

    public function test_evaluasi_project_show_api_returns_mata_kuliah_and_evaluator_details(): void
    {
        $mk = MataKuliah::create([
            'kode_matkul' => 'MK406',
            'nama_matkul' => 'Pengujian Perangkat Lunak',
            'semester' => 4,
            'sks' => 3,
            'is_active' => true,
        ]);
        $mk->dosenPengampu()->attach([$this->dosen->id]);

        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'Automated QA Pipeline',
            'status_proyek' => 'Proses',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach([$mk->id]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.evaluasi.project.show', $card));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('project.mata_kuliahs.0.kode_matkul', 'MK406');
        $response->assertJsonPath('project.mata_kuliahs.0.dosen_pengampu.0.nama_user', $this->dosen->nama_user);
    }

    public function test_evaluator_can_view_evaluasi_detail_with_mata_kuliah_and_evaluators(): void
    {
        $evaluatorUser = User::create([
            'nama_user' => 'Dosen Evaluator PBL',
            'username' => 'evaluator_test',
            'email' => 'evaluator@politala.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'no_hp' => '0812999999',
        ]);

        $mk = MataKuliah::create([
            'kode_matkul' => 'MK407',
            'nama_matkul' => 'Desain Antarmuka Pengguna',
            'semester' => 4,
            'sks' => 3,
            'is_active' => true,
        ]);
        $mk->dosenPengampu()->attach([$this->dosen->id]);

        $card = ProjectCard::create([
            'kelompok_id' => $this->kelompok->id,
            'periode_id' => $this->periode->id,
            'list_id' => $this->list->id,
            'title' => 'UI Redesign Portal Kampus',
            'status_proyek' => 'Proses',
            'created_by' => $this->studentUser->id,
        ]);
        $card->mataKuliahs()->attach([$mk->id]);

        $response = $this->actingAs($evaluatorUser)->get(route('dosen.evaluasi.showKelompok', $this->kelompok));
        $response->assertStatus(200);
        $response->assertSee('Informasi Mata Kuliah Terkait');
        $response->assertSee('MK407');
        $response->assertSee('Desain Antarmuka Pengguna');
        $response->assertSee('Siapa Saja yang Bisa Mengevaluasi');
    }
}
