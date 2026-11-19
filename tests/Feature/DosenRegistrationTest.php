<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DosenRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama_user' => 'Admin Sistem',
            'username' => 'admin_sistem',
            'email' => 'admin@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_hp' => '081234567890',
        ]);
    }

    /**
     * Test registration page displays role switcher and dosen registration form.
     */
    public function test_registration_page_displays_role_switcher_and_dosen_form(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Dosen / Pengajar');
        $response->assertSee('paneDosen');
        $response->assertSee('paneMahasiswa');

        $responseDosen = $this->get(route('register.dosen'));
        $responseDosen->assertStatus(200);
        $responseDosen->assertSee('Pendaftaran Akun Dosen / Pengajar');
        $responseDosen->assertSee('name="nip"', false);
    }

    /**
     * Test login page has links for both Mahasiswa and Dosen registration.
     */
    public function test_login_page_renders_dosen_and_mahasiswa_register_links(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee(route('register'));
        $response->assertSee(route('register.dosen'));
        $response->assertSee('Daftar Dosen');
        $response->assertSee('Daftar Mahasiswa');
    }

    /**
     * Test dosen can register with an unlinked NIP that exists in Bank Data Dosen.
     */
    public function test_dosen_can_register_with_existing_unlinked_nip_in_bank_data(): void
    {
        $dosen = Dosen::create([
            'nip' => '198801012015041001',
            'nama_dosen' => 'Dr. Hendra Wijaya, M.Kom.',
            'no_hp' => '081122334455',
            'keahlian' => 'Sistem Terdistribusi',
            'user_id' => null,
        ]);

        $response = $this->post(route('register.dosen.attempt'), [
            'nip' => '198801012015041001',
            'nama_user' => 'Dr. Hendra Wijaya, M.Kom.',
            'email' => 'hendra.wijaya@politala.ac.id',
            'no_hp' => '081122334455',
            'keahlian' => 'Sistem Terdistribusi & Komputasi Awan',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/dosen');

        // Check user created with role dosen
        $this->assertDatabaseHas('users', [
            'username' => '198801012015041001',
            'email' => 'hendra.wijaya@politala.ac.id',
            'role' => 'dosen',
            'nama_user' => 'Dr. Hendra Wijaya, M.Kom.',
        ]);

        $user = User::where('username', '198801012015041001')->first();
        $this->assertNotNull($user);

        // Check dosen bank data linked
        $dosen->refresh();
        $this->assertEquals($user->id, $dosen->user_id);
        $this->assertEquals('Sistem Terdistribusi & Komputasi Awan', $dosen->keahlian);

        // Check user is authenticated
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    /**
     * Test dosen can self-register even if not yet present in Bank Data Dosen.
     */
    public function test_dosen_can_register_even_if_not_yet_in_bank_data(): void
    {
        $response = $this->post(route('register.dosen.attempt'), [
            'nip' => '199205152020121002',
            'nama_user' => 'Siti Rahmawati, M.T.',
            'email' => 'siti.rahmawati@politala.ac.id',
            'no_hp' => '081299887766',
            'keahlian' => 'Kecerdasan Buatan',
            'password' => 'dosenpassword',
            'password_confirmation' => 'dosenpassword',
        ]);

        $response->assertRedirect('/dosen');

        // User must be created
        $this->assertDatabaseHas('users', [
            'username' => '199205152020121002',
            'email' => 'siti.rahmawati@politala.ac.id',
            'role' => 'dosen',
            'nama_user' => 'Siti Rahmawati, M.T.',
        ]);

        $user = User::where('username', '199205152020121002')->first();
        $this->assertNotNull($user);

        // Record must be auto-created in dosen table
        $this->assertDatabaseHas('dosen', [
            'user_id' => $user->id,
            'nip' => '199205152020121002',
            'nama_dosen' => 'Siti Rahmawati, M.T.',
            'keahlian' => 'Kecerdasan Buatan',
        ]);

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    /**
     * Test dosen registration fails when NIP is already taken by active user.
     */
    public function test_dosen_registration_fails_if_nip_already_claimed(): void
    {
        $existingUser = User::create([
            'nama_user' => 'Bambang Kusuma, M.Kom.',
            'username' => '198501012010121003',
            'email' => 'bambang@politala.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'no_hp' => '081234567899',
        ]);

        Dosen::create([
            'nip' => '198501012010121003',
            'nama_dosen' => 'Bambang Kusuma, M.Kom.',
            'user_id' => $existingUser->id,
        ]);

        $response = $this->from(route('register.dosen'))->post(route('register.dosen.attempt'), [
            'nip' => '198501012010121003',
            'nama_user' => 'Bambang Kusuma Baru',
            'email' => 'bambang.baru@politala.ac.id',
            'no_hp' => '081234567800',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('register.dosen'));
        $response->assertSessionHasErrors('nip');
        $response->assertSessionHas('existing_user_resolution');
    }

    /**
     * Test dosen registration fails if email is already taken.
     */
    public function test_dosen_registration_fails_if_email_already_registered(): void
    {
        User::create([
            'nama_user' => 'Existing Dosen',
            'username' => 'existing_dosen',
            'email' => 'registered@politala.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'no_hp' => '081234567899',
        ]);

        $response = $this->from(route('register.dosen'))->post(route('register.dosen.attempt'), [
            'nip' => '199901012022011005',
            'nama_user' => 'New Lecturer',
            'email' => 'registered@politala.ac.id',
            'no_hp' => '081234567800',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('register.dosen'));
        $response->assertSessionHasErrors('email');
        $response->assertSessionHas('existing_user_resolution');
    }

    /**
     * Test dosen can login using NIP, email, or username.
     */
    public function test_dosen_can_login_using_nip_and_email(): void
    {
        $dosenUser = User::create([
            'nama_user' => 'Ir. Agus Pratama, M.Eng.',
            'username' => '198003032005011002',
            'email' => 'agus.pratama@politala.ac.id',
            'password' => Hash::make('dosenpassword'),
            'role' => 'dosen',
            'no_hp' => '081344556677',
        ]);

        Dosen::create([
            'user_id' => $dosenUser->id,
            'nip' => '198003032005011002',
            'nama_dosen' => 'Ir. Agus Pratama, M.Eng.',
            'no_hp' => '081344556677',
        ]);

        // Login using NIP
        $responseNip = $this->post(route('login.attempt'), [
            'username' => '198003032005011002',
            'password' => 'dosenpassword',
        ]);
        $responseNip->assertRedirect('/dosen');
        $this->assertEquals($dosenUser->id, Auth::id());

        Auth::logout();

        // Login using Email
        $responseEmail = $this->post(route('login.attempt'), [
            'username' => 'agus.pratama@politala.ac.id',
            'password' => 'dosenpassword',
        ]);
        $responseEmail->assertRedirect('/dosen');
        $this->assertEquals($dosenUser->id, Auth::id());
    }

    /**
     * Test dosen without email can login directly using only NIP.
     */
    public function test_dosen_without_email_can_login_using_only_nip(): void
    {
        $dosenUser = User::create([
            'nama_user' => 'Dr. Hendra Tanpa Email',
            'username' => '197501012000011001',
            'email' => null,
            'password' => Hash::make('123456'),
            'role' => 'dosen',
            'no_hp' => '081234567800',
        ]);

        Dosen::create([
            'user_id' => $dosenUser->id,
            'nip' => '197501012000011001',
            'nama_dosen' => 'Dr. Hendra Tanpa Email',
            'no_hp' => '081234567800',
        ]);

        $response = $this->post(route('login.attempt'), [
            'username' => '197501012000011001',
            'password' => '123456',
        ]);

        $response->assertRedirect('/dosen');
        $this->assertEquals($dosenUser->id, Auth::id());
        $this->assertNull(Auth::user()->email);
    }

    /**
     * Test admin creating user with role dosen automatically syncs to Bank Data Dosen.
     */
    public function test_admin_creating_user_with_role_dosen_auto_syncs_to_bank_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('user.store'), [
            'nama_user' => 'Dra. Dewi Anggraeni, M.Si.',
            'email' => 'dewi@politala.ac.id',
            'username' => '197508102000032001',
            'no_hp' => '081299001122',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'dosen',
        ]);

        $response->assertRedirect(route('user.index'));

        $this->assertDatabaseHas('users', [
            'username' => '197508102000032001',
            'role' => 'dosen',
            'nama_user' => 'Dra. Dewi Anggraeni, M.Si.',
        ]);

        $createdUser = User::where('username', '197508102000032001')->first();
        $this->assertNotNull($createdUser);

        // Check auto creation in dosen table
        $this->assertDatabaseHas('dosen', [
            'user_id' => $createdUser->id,
            'nip' => '197508102000032001',
            'nama_dosen' => 'Dra. Dewi Anggraeni, M.Si.',
            'no_hp' => '081299001122',
        ]);
    }

    /**
     * Test admin updating user with role dosen syncs to Bank Data Dosen.
     */
    public function test_admin_updating_user_with_role_dosen_syncs_to_bank_data(): void
    {
        $dosenUser = User::create([
            'nama_user' => 'Nama Lama, M.Kom.',
            'username' => '198901012018011003',
            'email' => 'lama@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
            'no_hp' => '081111111111',
        ]);

        $dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'nip' => '198901012018011003',
            'nama_dosen' => 'Nama Lama, M.Kom.',
            'no_hp' => '081111111111',
        ]);

        $response = $this->actingAs($this->admin)->put(route('user.update', $dosenUser), [
            'nama_user' => 'Nama Baru, Dr., M.Kom.',
            'email' => 'baru@politala.ac.id',
            'username' => '198901012018011003',
            'no_hp' => '082222222222',
            'role' => 'dosen',
        ]);

        $response->assertRedirect(route('user.index'));

        $dosen->refresh();
        $this->assertEquals('Nama Baru, Dr., M.Kom.', $dosen->nama_dosen);
        $this->assertEquals('082222222222', $dosen->no_hp);
    }

    /**
     * Test admin editing unlinked dosen can create user account.
     */
    public function test_admin_editing_unlinked_dosen_can_create_user_account(): void
    {
        $dosen = Dosen::create([
            'nip' => '198305052009121004',
            'nama_dosen' => 'Fajar Sidik, S.T., M.T.',
            'no_hp' => '085233445566',
            'keahlian' => 'Robotika',
            'user_id' => null,
        ]);

        $response = $this->actingAs($this->admin)->put(route('dosen.update', $dosen), [
            'nip' => '198305052009121004',
            'nama_dosen' => 'Fajar Sidik, S.T., M.T.',
            'no_hp' => '085233445566',
            'keahlian' => 'Robotika & Kendali Cerdas',
            'create_user' => 1,
            'email' => 'fajar.sidik@politala.ac.id',
        ]);

        $response->assertRedirect(route('dosen.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'fajar.sidik@politala.ac.id',
            'username' => '198305052009121004',
            'role' => 'dosen',
        ]);

        $dosen->refresh();
        $this->assertNotNull($dosen->user_id);
        $this->assertEquals('fajar.sidik@politala.ac.id', $dosen->user->email);
        $this->assertEquals('Robotika & Kendali Cerdas', $dosen->keahlian);
    }

    /**
     * Test dosen updating their profile syncs changes to Bank Data Dosen.
     */
    public function test_dosen_updating_profile_syncs_to_bank_data(): void
    {
        $dosenUser = User::create([
            'nama_user' => 'Rina Marlina, M.Kom.',
            'username' => '199104042019032007',
            'email' => 'rina@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
            'no_hp' => '087788990011',
        ]);

        $dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'nip' => '199104042019032007',
            'nama_dosen' => 'Rina Marlina, M.Kom.',
            'no_hp' => '087788990011',
            'keahlian' => 'Basis Data',
        ]);

        $response = $this->actingAs($dosenUser)->put(route('profile.update'), [
            'nama_user' => 'Dr. Rina Marlina, M.Kom.',
            'email' => 'rina.marlina@politala.ac.id',
            'username' => '199104042019032007',
            'no_hp' => '089900112233',
            'keahlian' => 'Basis Data Lanjut & Big Data',
        ]);

        $response->assertRedirect(route('profile.edit'));

        $dosen->refresh();
        $this->assertEquals('Dr. Rina Marlina, M.Kom.', $dosen->nama_dosen);
        $this->assertEquals('089900112233', $dosen->no_hp);
        $this->assertEquals('Basis Data Lanjut & Big Data', $dosen->keahlian);
    }
}
