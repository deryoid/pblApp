<?php

namespace Tests\Feature;

use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MataKuliahImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $dosen1;

    protected User $dosen2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama_user' => 'Administrator PBL',
            'username' => 'admin_test',
            'email' => 'admin@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_hp' => '081234567890',
        ]);

        $this->dosen1 = User::create([
            'nama_user' => 'Dr. Ahmad Fauzi, M.Kom.',
            'username' => '198501012010121001',
            'email' => 'ahmad@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
            'no_hp' => '081234567891',
        ]);

        $this->dosen2 = User::create([
            'nama_user' => 'Nurul Hidayati, S.Kom., M.T.',
            'username' => '199003152018032002',
            'email' => 'nurul@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
            'no_hp' => '085678901234',
        ]);
    }

    /**
     * Admin can view the Mata Kuliah import form.
     */
    public function test_admin_can_view_matakuliah_import_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('matakuliah.import.form'));

        $response->assertStatus(200);
        $response->assertSee('Import Data Mata Kuliah');
        $response->assertSee('Mulai Import');
    }

    /**
     * Admin can download excel template for mata kuliah.
     */
    public function test_admin_can_download_matakuliah_excel_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('matakuliah.template.excel'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    /**
     * Admin can download csv template for mata kuliah.
     */
    public function test_admin_can_download_matakuliah_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('matakuliah.template.csv'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    /**
     * Admin can import mata kuliah from CSV and sync dosen pengampu.
     */
    public function test_admin_can_import_matakuliah_from_csv_with_dosen_linking(): void
    {
        $csvContent = "Kode MK,Nama Mata Kuliah,Semester,SKS,Dosen Pengampu,Deskripsi\n";
        $csvContent .= "TRKJ101,Jaringan Komputer Dasar,1,3,198501012010121001; Nurul Hidayati,Pengenalan jaringan dasar\n";
        $csvContent .= "TRKJ203,Pemrograman Web Lanjut,3,4,198501012010121001,Pengembangan aplikasi web berbasis framework\n";
        $csvContent .= 'TRKJ305,Kriptografi dan Keamanan Data,5,2,,Dasar-dasar enkripsi dan keamanan data';

        $tmpFile = tempnam(sys_get_temp_dir(), 'mk_').'.csv';
        file_put_contents($tmpFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'matakuliah.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('matakuliah.import'), [
            'file' => $uploadedFile,
        ]);

        $response->assertRedirect(route('matakuliah.import.form'));

        $this->assertDatabaseHas('mata_kuliah', [
            'kode_matkul' => 'TRKJ101',
            'nama_matkul' => 'Jaringan Komputer Dasar',
            'semester' => 1,
            'sks' => 3,
        ]);

        $this->assertDatabaseHas('mata_kuliah', [
            'kode_matkul' => 'TRKJ203',
            'nama_matkul' => 'Pemrograman Web Lanjut',
            'semester' => 3,
            'sks' => 4,
        ]);

        $this->assertDatabaseHas('mata_kuliah', [
            'kode_matkul' => 'TRKJ305',
            'nama_matkul' => 'Kriptografi dan Keamanan Data',
            'semester' => 5,
            'sks' => 2,
        ]);

        // Check pivot table dosen_mata_kuliah
        $mk1 = MataKuliah::where('kode_matkul', 'TRKJ101')->first();
        $this->assertNotNull($mk1);
        $dosenIds = $mk1->dosenPengampu->pluck('id')->all();
        $this->assertContains($this->dosen1->id, $dosenIds);
        $this->assertContains($this->dosen2->id, $dosenIds);

        $mk2 = MataKuliah::where('kode_matkul', 'TRKJ203')->first();
        $this->assertNotNull($mk2);
        $dosenIds2 = $mk2->dosenPengampu->pluck('id')->all();
        $this->assertContains($this->dosen1->id, $dosenIds2);

        @unlink($tmpFile);
    }

    /**
     * Non-admin cannot access mata kuliah import form.
     */
    public function test_non_admin_cannot_access_import_matakuliah(): void
    {
        $response = $this->actingAs($this->dosen1)->get(route('matakuliah.import.form'));
        $response->assertStatus(302);
    }
}
