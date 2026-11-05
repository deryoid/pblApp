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
}
