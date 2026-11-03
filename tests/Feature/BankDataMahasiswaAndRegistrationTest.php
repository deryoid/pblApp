<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Services\MahasiswaImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BankDataMahasiswaAndRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama_user' => 'Super Administrator',
            'username' => 'admin_tester',
            'email' => 'admin@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_hp' => '081234567890',
        ]);
    }

    /**
     * Tes import file CSV memasukkan data ke Bank Data Mahasiswa tanpa membuat user palsu (user_id = null).
     */
    public function test_import_creates_bank_data_mahasiswa_with_null_user_id_when_user_does_not_exist(): void
    {
        $csvContent = "NIM,Nama\n";
        $csvContent .= "C030324001,Ahmad Santoso\n";
        $csvContent .= "C030324002,Budi Raharjo\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_import_');
        file_put_contents($tmpFile, $csvContent);

        $service = new MahasiswaImportService;
        $result = $service->importFromCsv($tmpFile);

        @unlink($tmpFile);

        $this->assertEquals(2, $result['stats']['created_mhs']);
        $this->assertEmpty($result['errors']);

        // Pastikan record masuk ke tabel mahasiswa sebagai Bank Data Mahasiswa
        $this->assertDatabaseHas('mahasiswa', [
            'nim' => 'C030324001',
            'nama_mahasiswa' => 'Ahmad Santoso',
            'user_id' => null,
        ]);

        $this->assertDatabaseHas('mahasiswa', [
            'nim' => 'C030324002',
            'nama_mahasiswa' => 'Budi Raharjo',
            'user_id' => null,
        ]);

        // Pastikan tidak ada user yang di-generate sembarangan
        $this->assertDatabaseMissing('users', [
            'username' => 'C030324001',
        ]);
    }

    /**
     * Tes import file CSV saat akun user sudah ada: nama user disesuaikan dan user_id ditautkan.
     */
    public function test_import_syncs_user_name_and_links_user_id_when_user_already_exists(): void
    {
        // Buat user terlebih dahulu dengan nama awal
        $existingUser = User::create([
            'nama_user' => 'Ahmad Nama Lama',
            'username' => 'C030324099',
            'email' => 'ahmad@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'mahasiswa',
            'no_hp' => '0899999999',
        ]);

        $csvContent = "NIM,Nama\n";
        $csvContent .= "C030324099,Ahmad Nama Resmi Bank Data\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_import_');
        file_put_contents($tmpFile, $csvContent);

        $service = new MahasiswaImportService;
        $result = $service->importFromCsv($tmpFile);

        @unlink($tmpFile);

        $this->assertEquals(1, $result['stats']['created_mhs']);
        $this->assertEquals(1, $result['stats']['attached']);

        // Pastikan nama user terupdate mengikuti bank data
        $existingUser->refresh();
        $this->assertEquals('Ahmad Nama Resmi Bank Data', $existingUser->nama_user);

        // Pastikan mahasiswa terhubung dengan user id
        $this->assertDatabaseHas('mahasiswa', [
            'nim' => 'C030324099',
            'nama_mahasiswa' => 'Ahmad Nama Resmi Bank Data',
            'user_id' => $existingUser->id,
        ]);
    }

    /**
     * Tes halaman pendaftaran mahasiswa dapat diakses secara publik.
     */
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Akun Mahasiswa');
        $response->assertSee('Bank Data Mahasiswa');
    }

    /**
     * Tes pendaftaran gagal jika NIM mahasiswa tidak ada di Bank Data Mahasiswa.
     */
    public function test_registration_fails_if_nim_not_in_bank_data(): void
    {
        $response = $this->from('/register')->post('/register', [
            'nim' => 'C030324999', // NIM tidak ada di bank data
            'email' => 'unknown@politala.ac.id',
            'no_hp' => '08123456789',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('nim');
        $this->assertGuest();
    }

    /**
     * Tes pendaftaran gagal jika NIM sudah memiliki akun aktif / user_id tidak null.
     */
    public function test_registration_fails_if_nim_already_claimed(): void
    {
        $user = User::create([
            'nama_user' => 'Siti Aminah',
            'username' => 'C030324010',
            'email' => 'siti@example.com',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '0812333333',
        ]);

        Mahasiswa::create([
            'nim' => 'C030324010',
            'nama_mahasiswa' => 'Siti Aminah',
            'user_id' => $user->id, // Sudah punya akun
            'semester' => 3,
        ]);

        $response = $this->from('/register')->post('/register', [
            'nim' => 'C030324010',
            'email' => 'siti_baru@politala.ac.id',
            'no_hp' => '0812333333',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('nim');
        $this->assertGuest();
    }

    /**
     * Tes pendaftaran berhasil jika NIM valid di Bank Data Mahasiswa dan belum memiliki akun.
     */
    public function test_registration_succeeds_for_valid_unlinked_nim_in_bank_data(): void
    {
        $kelas = Kelas::create([
            'kelas' => 'TRKJ 3A',
            'semester' => 3,
        ]);

        // Simpan data calon mahasiswa di Bank Data Mahasiswa (tanpa akun user)
        $bankMhs = Mahasiswa::create([
            'nim' => 'C030324077',
            'nama_mahasiswa' => 'Muhammad Rizki Pratama',
            'kelas_id' => $kelas->id,
            'user_id' => null,
            'semester' => 3,
        ]);

        $response = $this->post('/register', [
            'nim' => 'c030324077', // Uji case-insensitivity
            'email' => 'rizki@politala.ac.id',
            'no_hp' => '081345678901',
            'password' => 'aman12345',
            'password_confirmation' => 'aman12345',
        ]);

        $response->assertRedirect('/mahasiswa');
        $this->assertAuthenticated();

        // Verifikasi akun User dibuat dengan nama resmi dari Bank Data Mahasiswa
        $createdUser = User::where('username', 'C030324077')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('Muhammad Rizki Pratama', $createdUser->nama_user);
        $this->assertEquals('rizki@politala.ac.id', $createdUser->email);
        $this->assertEquals('mahasiswa', $createdUser->role);

        // Verifikasi relasi Bank Data Mahasiswa terhubung ke user id
        $bankMhs->refresh();
        $this->assertEquals($createdUser->id, $bankMhs->user_id);
        $this->assertTrue($bankMhs->is_profile_complete);
    }

    /**
     * Tes mahasiswa dapat login secara normal tanpa terblokir bug lockout login.
     */
    public function test_mahasiswa_can_login_without_lockout_bug(): void
    {
        $user = User::create([
            'nama_user' => 'Dewi Lestari',
            'username' => 'C030324050',
            'email' => 'dewi@politala.ac.id',
            'password' => Hash::make('mypassword123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567800',
        ]);

        Mahasiswa::create([
            'nim' => 'C030324050',
            'nama_mahasiswa' => 'Dewi Lestari',
            'user_id' => $user->id,
            'semester' => 1,
        ]);

        $response = $this->post('/login', [
            'username' => 'C030324050',
            'password' => 'mypassword123',
        ]);

        $response->assertRedirect('/mahasiswa');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Tes administrator dapat mendaftarkan akun untuk role lain (dosen, evaluator, admin).
     */
    public function test_admin_can_create_other_roles_dosen_evaluator_admin(): void
    {
        // 1. Buat role dosen
        $resDosen = $this->actingAs($this->admin)->post('/admin/user', [
            'nama_user' => 'Bapak Dosen Pengampu, M.Kom',
            'username' => 'dosen_pengampu_1',
            'email' => 'dosen1@politala.ac.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'dosen',
            'no_hp' => '081234000001',
        ]);
        $resDosen->assertRedirect('/admin/user');
        $this->assertDatabaseHas('users', [
            'username' => 'dosen_pengampu_1',
            'role' => 'dosen',
        ]);

        // 2. Buat role evaluator
        $resEvaluator = $this->actingAs($this->admin)->post('/admin/user', [
            'nama_user' => 'Ibu Penilai Evaluator, M.T',
            'username' => 'evaluator_proyek_1',
            'email' => 'evaluator1@politala.ac.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'evaluator',
            'no_hp' => '081234000002',
        ]);
        $resEvaluator->assertRedirect('/admin/user');
        $this->assertDatabaseHas('users', [
            'username' => 'evaluator_proyek_1',
            'role' => 'evaluator',
        ]);

        // 3. Buat role admin
        $resAdmin = $this->actingAs($this->admin)->post('/admin/user', [
            'nama_user' => 'Admin Baru',
            'username' => 'admin_baru_trkj',
            'email' => 'admin_baru@politala.ac.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
            'no_hp' => '081234000003',
        ]);
        $resAdmin->assertRedirect('/admin/user');
        $this->assertDatabaseHas('users', [
            'username' => 'admin_baru_trkj',
            'role' => 'admin',
        ]);
    }
}
