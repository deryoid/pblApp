<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Services\MahasiswaImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MahasiswaExcelImportAndUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama_user' => 'Admin Mahasiswa',
            'username' => 'admin_mhs',
            'email' => 'admin_mhs@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_hp' => '081234567890',
        ]);
    }

    /**
     * Admin can download the Excel import template.
     */
    public function test_admin_can_download_excel_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('mahasiswa.template.excel'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('template-mahasiswa.xlsx', (string) $response->headers->get('content-disposition'));
    }

    /**
     * Importing from an Excel (.xlsx) file populates all 13 fields correctly.
     */
    public function test_import_from_excel_creates_mahasiswa_with_all_detailed_fields(): void
    {
        Kelas::create(['kelas' => '2A', 'semester' => 2]);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // 13 headers as requested
        $headers = [
            'Angkatan', 'NIM', 'Nama Mahasiswa', 'NIK', 'JK', 'Nama Ibu',
            'Agama', 'Kelas', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat',
            'No. HP', 'Email Kampus',
        ];
        $sheet->fromArray([$headers], null, 'A1');

        $row1 = [
            '2024',
            'C030324101',
            'Muhammad Fauzan',
            '6301011504040005',
            'Laki-laki',
            'Siti Fatimah',
            'Islam',
            '2A',
            'Pelaihari',
            '2004-04-15',
            'Jl. Angsau No. 10',
            '081234567899',
            'c030324101@politala.ac.id',
        ];
        $sheet->fromArray([$row1], null, 'A2');

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_mhs_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        $service = new MahasiswaImportService;
        $result = $service->importFile($tmpFile, 'xlsx');

        @unlink($tmpFile);

        $this->assertEquals(1, $result['stats']['created_mhs']);
        $this->assertEmpty($result['errors']);

        $this->assertDatabaseHas('mahasiswa', [
            'nim' => 'C030324101',
            'nama_mahasiswa' => 'Muhammad Fauzan',
            'angkatan' => '2024',
            'nik' => '6301011504040005',
            'jk' => 'L',
            'nama_ibu' => 'Siti Fatimah',
            'agama' => 'Islam',
            'tempat_lahir' => 'Pelaihari',
            'alamat' => 'Jl. Angsau No. 10',
            'no_hp' => '081234567899',
            'email_kampus' => 'c030324101@politala.ac.id',
        ]);

        $mhs1 = Mahasiswa::where('nim', 'C030324101')->first();
        $this->assertNotNull($mhs1);
        $this->assertEquals('2004-04-15', $mhs1->tanggal_lahir?->format('Y-m-d'));
    }

    /**
     * Importing from CSV via the admin endpoint stores all detailed fields.
     */
    public function test_admin_can_import_csv_with_all_fields_via_controller(): void
    {
        $kelas = Kelas::create(['kelas' => '3B', 'semester' => 3]);

        $csv = "Angkatan,NIM,Nama Mahasiswa,NIK,JK,Nama Ibu,Agama,Kelas,Tempat Lahir,Tanggal Lahir,Alamat,No. HP,Email Kampus\n"
            ."2023,C030323055,Dewi Sartika,6301015509030009,P,Halimah,Islam,3B,Banjarmasin,2003-09-15,\"Jl. Veteran No. 44\",085299887766,dewi@politala.ac.id\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_').'.csv';
        file_put_contents($tmpFile, $csv);

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'mahasiswa.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('mahasiswa.import'), [
            'file' => $uploadedFile,
        ]);

        $response->assertRedirect(route('mahasiswa.import.form'));

        $this->assertDatabaseHas('mahasiswa', [
            'nim' => 'C030323055',
            'nama_mahasiswa' => 'Dewi Sartika',
            'angkatan' => '2023',
            'nik' => '6301015509030009',
            'jk' => 'P',
            'nama_ibu' => 'Halimah',
            'kelas_id' => $kelas->id,
            'tempat_lahir' => 'Banjarmasin',
            'alamat' => 'Jl. Veteran No. 44',
            'no_hp' => '085299887766',
            'email_kampus' => 'dewi@politala.ac.id',
        ]);

        $mhs2 = Mahasiswa::where('nim', 'C030323055')->first();
        $this->assertNotNull($mhs2);
        $this->assertEquals('2003-09-15', $mhs2->tanggal_lahir?->format('Y-m-d'));
    }

    /**
     * Mahasiswa can update their own data through the profile edit endpoint.
     */
    public function test_mahasiswa_can_update_own_detailed_data(): void
    {
        $kelas = Kelas::create(['kelas' => '2A', 'semester' => 2]);
        $newKelas = Kelas::create(['kelas' => '2B', 'semester' => 2]);

        $studentUser = User::create([
            'nama_user' => 'Mahasiswa Test',
            'username' => 'C030324888',
            'email' => 'student@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '0811111111',
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $studentUser->id,
            'nim' => 'C030324888',
            'nama_mahasiswa' => 'Mahasiswa Test',
            'kelas_id' => $kelas->id,
            'angkatan' => '2023',
            'semester' => 2,
            'is_profile_complete' => true,
        ]);

        $response = $this->actingAs($studentUser)->put(route('profile.update'), [
            'nama_user' => 'Mahasiswa Nama Baru',
            'username' => 'c030324888',
            'email' => 'student_updated@politala.ac.id',
            'no_hp' => '081299998888',
            'nim' => 'C030324888',
            'angkatan' => '2024',
            'kelas_id' => $newKelas->id,
            'nik' => '6301012202040001',
            'jk' => 'L',
            'nama_ibu' => 'Ibu Kandung Saya',
            'agama' => 'Islam',
            'tempat_lahir' => 'Tanah Laut',
            'tanggal_lahir' => '2004-02-22',
            'alamat' => 'Komplek Permata Indah Blok C',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status');

        // Verify User record updated
        $this->assertDatabaseHas('users', [
            'id' => $studentUser->id,
            'nama_user' => 'Mahasiswa Nama Baru',
            'email' => 'student_updated@politala.ac.id',
            'no_hp' => '081299998888',
        ]);

        // Verify Mahasiswa record updated with all detailed fields
        $this->assertDatabaseHas('mahasiswa', [
            'id' => $mahasiswa->id,
            'nama_mahasiswa' => 'Mahasiswa Nama Baru',
            'angkatan' => '2024',
            'kelas_id' => $newKelas->id,
            'nik' => '6301012202040001',
            'jk' => 'L',
            'nama_ibu' => 'Ibu Kandung Saya',
            'agama' => 'Islam',
            'tempat_lahir' => 'Tanah Laut',
            'alamat' => 'Komplek Permata Indah Blok C',
            'no_hp' => '081299998888',
            'email_kampus' => 'student_updated@politala.ac.id',
            'is_profile_complete' => 1,
        ]);

        $this->assertEquals('2004-02-22', $mahasiswa->fresh()->tanggal_lahir?->format('Y-m-d'));
    }
}
