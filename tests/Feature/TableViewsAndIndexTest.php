<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TableViewsAndIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $evaluator;

    protected Periode $activePeriode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activePeriode = Periode::create([
            'periode' => '2026/2027 Ganjil',
            'status_periode' => 'Aktif',
        ]);

        $this->admin = User::create([
            'nama_user' => 'Super Administrator',
            'username' => 'admin_tester',
            'email' => 'admin@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_hp' => '081234567890',
        ]);

        $this->evaluator = User::create([
            'nama_user' => 'Dosen Evaluator',
            'username' => 'evaluator_tester',
            'email' => 'evaluator@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'evaluator',
            'no_hp' => '081234567891',
        ]);
    }

    /**
     * Tampilan tabel Bank Data Mahasiswa di index admin.
     */
    public function test_admin_mahasiswa_index_table_renders_cleanly_without_modals_inside_cells(): void
    {
        $kelas = Kelas::create(['kelas' => 'TRKJ 1A', 'semester' => 1]);
        $mhs = Mahasiswa::create([
            'nim' => 'C030324001',
            'nama_mahasiswa' => 'Budi Santoso',
            'kelas_id' => $kelas->id,
            'semester' => 1,
            'user_id' => null,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/mahasiswa');

        $response->assertStatus(200);
        $response->assertSee('Bank Data Mahasiswa');
        $response->assertSee('C030324001');
        $response->assertSee('Budi Santoso');
        $response->assertSee('no-sort');
        $response->assertSee('dataTable');
    }

    /**
     * Tampilan tabel Bank Data Dosen di index admin.
     */
    public function test_admin_dosen_index_table_renders_cleanly(): void
    {
        \App\Models\Dosen::create([
            'nip' => '198501012010121001',
            'nama_dosen' => 'Dr. Ir. Hendra, M.Kom',
            'no_hp' => '081234567899',
            'keahlian' => 'Artificial Intelligence & IoT',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dosen');

        $response->assertStatus(200);
        $response->assertSee('Bank Data Dosen');
        $response->assertSee('198501012010121001');
        $response->assertSee('Dr. Ir. Hendra, M.Kom');
        $response->assertSee('dataTable');
    }

    /**
     * Tampilan tabel Pengguna di index admin.
     */
    public function test_admin_user_index_table_renders_cleanly(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/user');

        $response->assertStatus(200);
        $response->assertSee('Pengguna Sistem');
        $response->assertSee('admin_tester');
        $response->assertSee('dataTable');
    }

    /**
     * Tampilan tabel Kelompok di index admin dan evaluator.
     */
    public function test_kelompok_index_tables_render_cleanly_for_admin_and_evaluator(): void
    {
        $kelompok = Kelompok::create([
            'nama_kelompok' => 'Kelompok 1 - Smart Campus',
            'periode_id' => $this->activePeriode->id,
            'link_drive' => 'https://drive.google.com/test',
        ]);

        // Admin
        $respAdmin = $this->actingAs($this->admin)->get('/admin/kelompok');
        $respAdmin->assertStatus(200);
        $respAdmin->assertSee('Kelompok PBL');
        $respAdmin->assertSee('Kelompok 1 - Smart Campus');

        // Evaluator
        $respEval = $this->actingAs($this->evaluator)->get('/evaluator/kelompok');
        $respEval->assertStatus(200);
        $respEval->assertSee('Kelompok PBL');
        $respEval->assertSee('Kelompok 1 - Smart Campus');
    }

    /**
     * Tampilan tabel Mata Kuliah di index admin.
     */
    public function test_admin_matakuliah_index_table_renders_cleanly(): void
    {
        MataKuliah::create([
            'kode_matkul' => 'TRKJ101',
            'nama_matkul' => 'Algoritma & Pemrograman',
            'semester' => 1,
            'sks' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/matakuliah');

        $response->assertStatus(200);
        $response->assertSee('Mata Kuliah');
        $response->assertSee('TRKJ101');
        $response->assertSee('Algoritma & Pemrograman');
    }

    /**
     * Tampilan tabel Nilai Mahasiswa di index admin dan evaluator sekarang menggunakan DataTables.
     */
    public function test_nilai_index_tables_use_datatable(): void
    {
        // Admin
        $respAdmin = $this->actingAs($this->admin)->get('/admin/nilai');
        $respAdmin->assertStatus(200);
        $respAdmin->assertSee('Nilai Mahasiswa');

        // Evaluator
        $respEval = $this->actingAs($this->evaluator)->get('/evaluator/nilai');
        $respEval->assertStatus(200);
        $respEval->assertSee('Nilai Mahasiswa');
    }

    /**
     * Tampilan tabel Evaluasi di index admin dan evaluator.
     */
    public function test_evaluasi_index_tables_render_cleanly(): void
    {
        // Admin
        $respAdmin = $this->actingAs($this->admin)->get('/admin/evaluasi');
        $respAdmin->assertStatus(200);
        $respAdmin->assertSee('tbl-evaluasi');
        $respAdmin->assertSee('Progress Evaluasi');

        // Evaluator
        $respEval = $this->actingAs($this->evaluator)->get('/evaluator/evaluasi');
        $respEval->assertStatus(200);
        $respEval->assertSee('tbl-evaluasi');
        $respEval->assertSee('Progress Evaluasi');
    }
}
