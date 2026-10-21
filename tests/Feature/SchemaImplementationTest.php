<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokJoinRequest;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\PenilaianProyekDosen;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectDosenPenilai;
use App\Models\RubrikKategori;
use App\Models\User;
use Database\Seeders\RubrikKategoriSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaImplementationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RubrikKategoriSeeder::class);
    }

    public function test_user_can_have_dosen_role_and_google_credentials(): void
    {
        $dosen = User::create([
            'nama_user' => 'Dr. Budi Santoso',
            'username' => 'dosen_budi',
            'email' => 'budi@politala.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'dosen',
            'google_id' => 'google_oauth_123456',
            'avatar' => 'https://example.com/avatar.jpg',
            'no_hp' => '081234567890',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $dosen->id,
            'role' => 'dosen',
            'google_id' => 'google_oauth_123456',
        ]);
        $this->assertEquals('dosen', $dosen->role);
    }

    public function test_mahasiswa_and_kelas_have_semester_support(): void
    {
        $user = User::create([
            'nama_user' => 'Ahmad Mahasiswa',
            'username' => 'ahmad_mhs',
            'email' => 'ahmad@example.com',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567891',
        ]);

        $kelas = Kelas::create([
            'kelas' => 'TRPL 3Z',
            'semester' => 3,
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'kelas_id' => $kelas->id,
            'nim' => '230411199999',
            'nama_mahasiswa' => 'Ahmad Mahasiswa',
            'semester' => 3,
            'is_profile_complete' => true,
        ]);

        $this->assertDatabaseHas('mahasiswa', [
            'id' => $mahasiswa->id,
            'semester' => 3,
            'is_profile_complete' => 1,
        ]);
        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'semester' => 3,
        ]);
    }

    public function test_kelompok_supports_semester_limits_and_join_requests(): void
    {
        $periode = Periode::firstOrCreate(
            ['periode' => '2026/2027 Ganjil'],
            ['status_periode' => 'Aktif']
        );

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Test PBL',
            'periode_id' => $periode->id,
            'semester' => 3,
            'max_members' => 4,
            'status_bergabung' => 'terbuka',
        ]);

        $user = User::create([
            'nama_user' => 'Pemohon Join',
            'username' => 'pemohon_join',
            'email' => 'pemohon@example.com',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567892',
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => '230411188888',
            'nama_mahasiswa' => 'Pemohon Join',
            'semester' => 3,
        ]);

        $request = KelompokJoinRequest::create([
            'kelompok_id' => $kelompok->id,
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'pending',
            'pesan' => 'Saya ingin bergabung dengan kelompok ini.',
        ]);

        $this->assertDatabaseHas('kelompok', [
            'id' => $kelompok->id,
            'semester' => 3,
            'max_members' => 4,
            'status_bergabung' => 'terbuka',
        ]);

        $this->assertDatabaseHas('kelompok_join_requests', [
            'id' => $request->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($kelompok->joinRequests->contains($request));
        $this->assertEquals($mahasiswa->id, $request->mahasiswa->id);
    }

    public function test_project_card_supports_internal_and_external_mechanisms(): void
    {
        $periode = Periode::firstOrCreate(
            ['periode' => '2026/2027 Ganjil'],
            ['status_periode' => 'Aktif']
        );

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Proyek Mekanisme',
            'periode_id' => $periode->id,
            'semester' => 4,
        ]);

        $dosen = User::create([
            'nama_user' => 'Dosen Pembimbing',
            'username' => 'dosen_pembimbing',
            'email' => 'pembimbing@example.com',
            'password' => bcrypt('password123'),
            'role' => 'dosen',
            'no_hp' => '081234567893',
        ]);

        // Proyek Internal
        $internalProject = ProjectCard::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
            'title' => 'Sistem Informasi Manajemen Aset',
            'jenis_proyek' => 'internal',
            'topik_proyek' => 'Internet of Things & Monitoring',
            'sumber_proyek' => 'dosen',
            'dosen_penentu_id' => $dosen->id,
        ]);

        // Proyek External
        $externalProject = ProjectCard::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
            'title' => 'Aplikasi POS Toko Berkah',
            'jenis_proyek' => 'external',
            'nama_mitra' => 'PT Mitra Solusi Digital',
            'kontak_mitra' => '082100001111',
            'alamat_mitra' => 'Jl. Pelaihari No. 12',
        ]);

        $this->assertDatabaseHas('project_cards', [
            'id' => $internalProject->id,
            'jenis_proyek' => 'internal',
            'sumber_proyek' => 'dosen',
            'dosen_penentu_id' => $dosen->id,
        ]);

        $this->assertDatabaseHas('project_cards', [
            'id' => $externalProject->id,
            'jenis_proyek' => 'external',
            'nama_mitra' => 'PT Mitra Solusi Digital',
        ]);

        $this->assertEquals($dosen->id, $internalProject->dosenPenentu->id);
    }

    public function test_mata_kuliah_checklist_relation_with_project_cards(): void
    {
        $matkul = MataKuliah::create([
            'kode_matkul' => 'MK999',
            'nama_matkul' => 'Pengujian Perangkat Lunak Terapan',
            'semester' => 4,
            'sks' => 3,
        ]);

        $periode = Periode::firstOrCreate(
            ['periode' => '2026/2027 Ganjil'],
            ['status_periode' => 'Aktif']
        );

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Matkul Test',
            'periode_id' => $periode->id,
            'semester' => 4,
        ]);

        $project = ProjectCard::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
            'title' => 'Proyek Uji Matkul',
            'jenis_proyek' => 'internal',
        ]);

        $project->mataKuliahs()->attach($matkul->id);

        $this->assertDatabaseHas('project_mata_kuliah', [
            'project_card_id' => $project->id,
            'mata_kuliah_id' => $matkul->id,
        ]);

        $this->assertTrue($project->mataKuliahs->contains($matkul));
    }

    public function test_rubrik_four_categories_seeded_and_dosen_can_score_project(): void
    {
        $categories = RubrikKategori::whereIn('kode', [
            'kesesuaian_matkul',
            'kualitas_fungsi',
            'inovasi',
            'estetik',
        ])->get();

        $this->assertCount(4, $categories);

        $dosen = User::create([
            'nama_user' => 'Dosen Penilai Ahli',
            'username' => 'dosen_penilai',
            'email' => 'penilai@example.com',
            'password' => bcrypt('password123'),
            'role' => 'dosen',
            'no_hp' => '081234567894',
        ]);

        $periode = Periode::firstOrCreate(
            ['periode' => '2026/2027 Ganjil'],
            ['status_periode' => 'Aktif']
        );

        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok Penilaian Test',
            'periode_id' => $periode->id,
            'semester' => 3,
        ]);

        $project = ProjectCard::create([
            'kelompok_id' => $kelompok->id,
            'periode_id' => $periode->id,
            'title' => 'Proyek Dinilai Dosen',
            'jenis_proyek' => 'internal',
        ]);

        // Admin assigns dosen to project
        $assignment = ProjectDosenPenilai::create([
            'project_card_id' => $project->id,
            'dosen_id' => $dosen->id,
            'status_penilaian' => 'proses',
        ]);

        $this->assertDatabaseHas('project_dosen_penilai', [
            'id' => $assignment->id,
            'project_card_id' => $project->id,
            'dosen_id' => $dosen->id,
        ]);

        $kesesuaian = RubrikKategori::where('kode', 'kesesuaian_matkul')->first();
        $indikator = $kesesuaian->indikators()->first();

        // Dosen grades
        $nilai = PenilaianProyekDosen::create([
            'project_card_id' => $project->id,
            'dosen_id' => $dosen->id,
            'rubrik_kategori_id' => $kesesuaian->id,
            'rubrik_indikator_id' => $indikator?->id,
            'nilai' => 88.50,
            'catatan_umpan_balik' => 'Implementasi sangat baik sesuai kurikulum',
        ]);

        $this->assertDatabaseHas('penilaian_proyek_dosen', [
            'id' => $nilai->id,
            'nilai' => 88.50,
        ]);

        $this->assertEquals($dosen->id, $nilai->dosen->id);
        $this->assertEquals($project->id, $nilai->projectCard->id);
    }
}
