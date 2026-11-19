<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BankDataDosenTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

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
    }

    /**
     * Admin can view the Bank Data Dosen index page.
     */
    public function test_admin_can_view_bank_data_dosen_index(): void
    {
        Dosen::create([
            'nip' => '198501012010121001',
            'nama_dosen' => 'Dr. Ahmad Fauzi, M.Kom.',
            'no_hp' => '081234567891',
            'keahlian' => 'Kecerdasan Buatan & Sistem Cerdas',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dosen.index'));

        $response->assertStatus(200);
        $response->assertSee('Bank Data Dosen');
        $response->assertSee('198501012010121001');
        $response->assertSee('Dr. Ahmad Fauzi, M.Kom.');
        $response->assertSee('Kecerdasan Buatan & Sistem Cerdas');
    }

    /**
     * Admin can create a new Dosen record without an account.
     */
    public function test_admin_can_create_dosen_without_user_account(): void
    {
        $response = $this->actingAs($this->admin)->post(route('dosen.store'), [
            'nip' => '199002022015041002',
            'nama_dosen' => 'Nurul Hidayati, S.T., M.Cs.',
            'no_hp' => '081398765432',
            'keahlian' => 'Jaringan Komputer & Cloud Computing',
            'create_user' => 0,
        ]);

        $response->assertRedirect(route('dosen.index'));

        $this->assertDatabaseHas('dosen', [
            'nip' => '199002022015041002',
            'nama_dosen' => 'Nurul Hidayati, S.T., M.Cs.',
            'no_hp' => '081398765432',
            'keahlian' => 'Jaringan Komputer & Cloud Computing',
            'user_id' => null,
        ]);
    }

    /**
     * Admin can create a new Dosen and automatically generate a user login account.
     */
    public function test_admin_can_create_dosen_with_user_account(): void
    {
        $response = $this->actingAs($this->admin)->post(route('dosen.store'), [
            'nip' => '198203032008121003',
            'nama_dosen' => 'Bambang Pratama, M.T.',
            'no_hp' => '085211223344',
            'keahlian' => 'Embedded System & Internet of Things',
            'email' => 'bambang@politala.ac.id',
            'create_user' => 1,
        ]);

        $response->assertRedirect(route('dosen.index'));

        $this->assertDatabaseHas('users', [
            'username' => '198203032008121003',
            'email' => 'bambang@politala.ac.id',
            'role' => 'dosen',
            'nama_user' => 'Bambang Pratama, M.T.',
        ]);

        $user = User::where('username', '198203032008121003')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('123456', $user->password));

        $this->assertDatabaseHas('dosen', [
            'nip' => '198203032008121003',
            'nama_dosen' => 'Bambang Pratama, M.T.',
            'user_id' => $user->id,
            'no_hp' => '085211223344',
            'keahlian' => 'Embedded System & Internet of Things',
        ]);
    }

    /**
     * Admin can update Dosen data and synchronize with linked User account.
     */
    public function test_admin_can_update_dosen_data(): void
    {
        $user = User::create([
            'nama_user' => 'Nama Dosen Lama',
            'username' => '198804042012011004',
            'email' => 'dosen_lama@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
            'no_hp' => '0811111111',
        ]);

        $dosen = Dosen::create([
            'user_id' => $user->id,
            'nip' => '198804042012011004',
            'nama_dosen' => 'Nama Dosen Lama',
            'no_hp' => '0811111111',
            'keahlian' => 'Rekayasa Perangkat Lunak',
        ]);

        $response = $this->actingAs($this->admin)->put(route('dosen.update', $dosen), [
            'nip' => '198804042012011004',
            'nama_dosen' => 'Dr. Nama Dosen Baru, M.T.',
            'no_hp' => '089988776655',
            'keahlian' => 'Cyber Security & DevSecOps',
        ]);

        $response->assertRedirect(route('dosen.index'));

        $this->assertDatabaseHas('dosen', [
            'id' => $dosen->id,
            'nama_dosen' => 'Dr. Nama Dosen Baru, M.T.',
            'no_hp' => '089988776655',
            'keahlian' => 'Cyber Security & DevSecOps',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nama_user' => 'Dr. Nama Dosen Baru, M.T.',
            'no_hp' => '089988776655',
        ]);
    }

    /**
     * Admin can delete Dosen data from Bank Data.
     */
    public function test_admin_can_delete_dosen_data(): void
    {
        $dosen = Dosen::create([
            'nip' => '197505052000031005',
            'nama_dosen' => 'Dosen Mau Dihapus, M.Cs.',
            'no_hp' => '081299887766',
            'keahlian' => 'Data Science',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('dosen.destroy', $dosen));

        $response->assertRedirect(route('dosen.index'));
        $this->assertDatabaseMissing('dosen', ['id' => $dosen->id]);
    }

    /**
     * Admin can synchronize existing Dosen users into Bank Data Dosen.
     */
    public function test_admin_can_sync_existing_dosen_users_to_bank_data(): void
    {
        $userDosen = User::create([
            'nama_user' => 'Dosen Dari User Table, M.Kom.',
            'username' => '198606062009121006',
            'email' => 'dosen_sync@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
            'no_hp' => '087766554433',
        ]);

        $response = $this->actingAs($this->admin)->post(route('dosen.sync'));

        $response->assertRedirect(route('dosen.index'));

        $this->assertDatabaseHas('dosen', [
            'user_id' => $userDosen->id,
            'nama_dosen' => 'Dosen Dari User Table, M.Kom.',
            'no_hp' => '087766554433',
        ]);
    }

    /**
     * Student cannot access Bank Data Dosen.
     */
    public function test_student_cannot_access_bank_data_dosen(): void
    {
        $student = User::create([
            'nama_user' => 'Siswa Test',
            'username' => 'C030324999',
            'email' => 'student@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '0811111111',
        ]);

        $response = $this->actingAs($student)->get(route('dosen.index'));

        $response->assertStatus(302);
    }

    /**
     * Admin can view dosen import form.
     */
    public function test_admin_can_view_dosen_import_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dosen.import.form'));

        $response->assertStatus(200);
        $response->assertSee('Import Bank Data Dosen');
        $response->assertSee('Mulai Import');
    }

    /**
     * Admin can download excel template for dosen.
     */
    public function test_admin_can_download_dosen_excel_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dosen.template.excel'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    /**
     * Admin can download csv template for dosen.
     */
    public function test_admin_can_download_dosen_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dosen.template.csv'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    /**
     * Admin can import dosen from CSV.
     */
    public function test_admin_can_import_dosen_from_csv(): void
    {
        $csvContent = "NIP,Nama Dosen,Nomor Telp,Keahlian\n";
        $csvContent .= "198811112015011001,Dr. Hendra Gunawan,081299887766,Jaringan Komputer & IoT\n";
        $csvContent .= '199205052020022002,Dewi Sartika M.Kom,85711223344,Sistem Informasi';

        $tmpFile = tempnam(sys_get_temp_dir(), 'dosen_').'.csv';
        file_put_contents($tmpFile, $csvContent);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tmpFile,
            'dosen.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('dosen.import'), [
            'file' => $uploadedFile,
        ]);

        $response->assertRedirect(route('dosen.import.form'));

        $this->assertDatabaseHas('dosen', [
            'nip' => '198811112015011001',
            'nama_dosen' => 'Dr. Hendra Gunawan',
            'no_hp' => '081299887766',
            'keahlian' => 'Jaringan Komputer & IoT',
        ]);

        $this->assertDatabaseHas('dosen', [
            'nip' => '199205052020022002',
            'nama_dosen' => 'Dewi Sartika M.Kom',
            'no_hp' => '085711223344',
            'keahlian' => 'Sistem Informasi',
        ]);

        // Verifikasi akun login otomatis dibuat dengan kata sandi 123456
        $user1 = User::where('username', '198811112015011001')->first();
        $this->assertNotNull($user1);
        $this->assertEquals('dosen', $user1->role);
        $this->assertTrue(Hash::check('123456', $user1->password));

        $user2 = User::where('username', '199205052020022002')->first();
        $this->assertNotNull($user2);
        $this->assertEquals('dosen', $user2->role);
        $this->assertTrue(Hash::check('123456', $user2->password));

        @unlink($tmpFile);
    }

    /**
     * Admin can batch generate accounts for all unlinked dosen with password 123456.
     */
    public function test_admin_can_batch_generate_dosen_accounts_with_default_password_123456(): void
    {
        $dosen1 = Dosen::create([
            'nip' => '198501012011011001',
            'nama_dosen' => 'Dosen Batch Satu, M.Kom.',
            'no_hp' => '0812340001',
        ]);

        $dosen2 = Dosen::create([
            'nip' => '198902022014022002',
            'nama_dosen' => 'Dosen Batch Dua, M.T.',
            'no_hp' => '0812340002',
        ]);

        $response = $this->actingAs($this->admin)->post(route('dosen.generate-accounts'));
        $response->assertRedirect(route('dosen.index'));

        $dosen1->refresh();
        $dosen2->refresh();

        $this->assertNotNull($dosen1->user_id);
        $this->assertNotNull($dosen2->user_id);

        $user1 = $dosen1->user;
        $this->assertEquals('198501012011011001', $user1->username);
        $this->assertNull($user1->email);
        $this->assertEquals('dosen', $user1->role);
        $this->assertTrue(Hash::check('123456', $user1->password));

        $user2 = $dosen2->user;
        $this->assertEquals('198902022014022002', $user2->username);
        $this->assertNull($user2->email);
        $this->assertEquals('dosen', $user2->role);
        $this->assertTrue(Hash::check('123456', $user2->password));

        // Dosen dapat login langsung hanya dengan NIP (tanpa email)
        $loginRes = $this->post(route('login.attempt'), [
            'username' => '198501012011011001',
            'password' => '123456',
        ]);
        $loginRes->assertRedirect(url('/dosen'));
        $this->assertAuthenticatedAs($user1);
    }

    /**
     * Admin can generate account for a single unlinked dosen with password 123456.
     */
    public function test_admin_can_generate_single_dosen_account_with_default_password_123456(): void
    {
        $dosen = Dosen::create([
            'nip' => '199303032019031003',
            'nama_dosen' => 'Dosen Tunggal, S.Kom., M.Cs.',
            'no_hp' => '085712345678',
        ]);

        $this->assertNull($dosen->user_id);

        $response = $this->actingAs($this->admin)->post(route('dosen.generate-account', $dosen));
        $response->assertRedirect(route('dosen.index'));

        $dosen->refresh();
        $this->assertNotNull($dosen->user_id);

        $user = $dosen->user;
        $this->assertEquals('199303032019031003', $user->username);
        $this->assertNull($user->email);
        $this->assertEquals('dosen', $user->role);
        $this->assertTrue(Hash::check('123456', $user->password));

        // Dosen tunggal dapat login langsung dengan NIP saja
        $loginRes = $this->post(route('login.attempt'), [
            'username' => '199303032019031003',
            'password' => '123456',
        ]);
        $loginRes->assertRedirect(url('/dosen'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Admin resetting dosen password sets password to 123456.
     */
    public function test_admin_reset_dosen_password_sets_to_123456(): void
    {
        $user = User::create([
            'nama_user' => 'Dosen Reset Password',
            'username' => '198704042010121004',
            'email' => 'reset_dosen@politala.ac.id',
            'password' => Hash::make('customOldPassword'),
            'role' => 'dosen',
            'no_hp' => '081234567890',
        ]);

        $response = $this->actingAs($this->admin)->post(route('user.reset-password', $user));
        $response->assertStatus(302);

        $user->refresh();
        $this->assertTrue(Hash::check('123456', $user->password));
    }

    /**
     * Artisan command dosen:generate-accounts successfully generates accounts with 123456.
     */
    public function test_artisan_dosen_generate_accounts_command_creates_accounts(): void
    {
        $dosen = Dosen::create([
            'nip' => '199505052021051005',
            'nama_dosen' => 'Dosen Artisan Command, M.Kom.',
            'no_hp' => '081234567899',
        ]);

        $this->artisan('dosen:generate-accounts')
            ->expectsOutputToContain('Berhasil membuat 1 akun dosen dengan kata sandi default: 123456')
            ->assertSuccessful();

        $dosen->refresh();
        $this->assertNotNull($dosen->user_id);

        $user = $dosen->user;
        $this->assertEquals('199505052021051005', $user->username);
        $this->assertNull($user->email);
        $this->assertTrue(Hash::check('123456', $user->password));

        // Verifikasi login hanya dengan NIP
        $loginRes = $this->post(route('login.attempt'), [
            'username' => '199505052021051005',
            'password' => '123456',
        ]);
        $loginRes->assertRedirect(url('/dosen'));
        $this->assertAuthenticatedAs($user);
    }
}
