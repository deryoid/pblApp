<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class AccountDuplicateAndResolutionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pendaftaran dengan NIM yang sudah memiliki akun aktif:
     * Menghasilkan pesan error, resolusi ke akun yang sudah ada, dan tetap guest.
     */
    public function test_register_with_already_claimed_nim_provides_resolution_and_fails_safely(): void
    {
        $existingUser = User::create([
            'nama_user' => 'Ahmad Santoso',
            'username' => 'C030324001',
            'email' => 'ahmad@politala.ac.id',
            'password' => Hash::make('secret123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567890',
        ]);

        Mahasiswa::create([
            'nim' => 'C030324001',
            'nama_mahasiswa' => 'Ahmad Santoso',
            'user_id' => $existingUser->id,
            'semester' => 3,
        ]);

        $response = $this->from('/register')->post('/register', [
            'nim' => 'C030324001',
            'email' => 'ahmad_baru@politala.ac.id',
            'no_hp' => '089988776655',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('nim');
        $response->assertSessionHas('existing_user_resolution');

        $resolution = session('existing_user_resolution');
        $this->assertEquals('C030324001', $resolution['login_key']);
        $this->assertEquals('Ahmad Santoso', $resolution['nama']);
        $this->assertGuest();
    }

    /**
     * Pendaftaran dengan email yang sudah terdaftar pada pengguna lain:
     * Menyediakan resolusi langsung untuk masuk dengan akun tersebut.
     */
    public function test_register_with_existing_email_provides_best_solution_resolution(): void
    {
        User::create([
            'nama_user' => 'Budi Raharjo',
            'username' => 'C030324002',
            'email' => 'budi@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567891',
        ]);

        Mahasiswa::create([
            'nim' => 'C030324003',
            'nama_mahasiswa' => 'Citra Lestari',
            'user_id' => null, // Belum punya akun
            'semester' => 3,
        ]);

        $response = $this->from('/register')->post('/register', [
            'nim' => 'C030324003',
            'email' => 'budi@politala.ac.id', // Pakai email yang sudah dipakai Budi
            'no_hp' => '089911223344',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $response->assertSessionHas('existing_user_resolution');

        $resolution = session('existing_user_resolution');
        $this->assertEquals('C030324002', $resolution['login_key']);
        $this->assertGuest();
    }

    /**
     * Mencegah akun ganda: Jika pengguna pernah login via Google OAuth (belum ada NIM),
     * lalu mengisi formulir registrasi dengan NIM Bank Data, akun Google ditautkan
     * secara otomatis dan disinkronkan tanpa membuat user duplikat!
     */
    public function test_register_auto_links_existing_google_oauth_account_to_bank_data(): void
    {
        $googleUser = User::create([
            'nama_user' => 'Dodi Google',
            'username' => 'dodi_google',
            'email' => 'dodi@politala.ac.id',
            'google_id' => 'google_oauth_12345',
            'role' => 'mahasiswa',
            'no_hp' => '-',
        ]);

        // Buat record dummy tanpa NIM yang biasa dibuat Google OAuth awal
        Mahasiswa::create([
            'user_id' => $googleUser->id,
            'nama_mahasiswa' => 'Dodi Google',
            'nim' => null,
            'semester' => 1,
            'is_profile_complete' => false,
        ]);

        // Record resmi di Bank Data Mahasiswa
        $kelas = Kelas::create(['kelas' => 'TRKJ 1A', 'semester' => 1]);
        $bankMhs = Mahasiswa::create([
            'nim' => 'C030324004',
            'nama_mahasiswa' => 'Dodi Hermawan',
            'kelas_id' => $kelas->id,
            'user_id' => null,
            'semester' => 1,
        ]);

        // Registrasi menggunakan NIM resmi Bank Data dan email Google yang sudah ada
        $response = $this->post('/register', [
            'nim' => 'C030324004',
            'email' => 'dodi@politala.ac.id',
            'no_hp' => '081234567899',
            'password' => 'passwordBaru123',
            'password_confirmation' => 'passwordBaru123',
        ]);

        $response->assertRedirect('/mahasiswa');
        $this->assertAuthenticated();

        // Pastikan jumlah user tetap 1 (tidak double!)
        $this->assertEquals(1, User::where('email', 'dodi@politala.ac.id')->count());

        // Pastikan username user terupdate menjadi NIM
        $googleUser->refresh();
        $this->assertEquals('C030324004', $googleUser->username);
        $this->assertEquals('Dodi Hermawan', $googleUser->nama_user);
        $this->assertEquals('081234567899', $googleUser->no_hp);

        // Pastikan bank data terhubung ke user id
        $bankMhs->refresh();
        $this->assertEquals($googleUser->id, $bankMhs->user_id);

        // Pastikan record dummy mahasiswa tanpa NIM telah dibersihkan
        $this->assertDatabaseMissing('mahasiswa', [
            'nim' => null,
            'user_id' => $googleUser->id,
        ]);
    }

    /**
     * Login mendukung username, NIM (case-insensitive), dan email.
     */
    public function test_login_supports_username_email_and_case_insensitive_nim(): void
    {
        $user = User::create([
            'nama_user' => 'Eka Pratama',
            'username' => 'C030324005',
            'email' => 'eka@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567892',
        ]);

        // 1. Login menggunakan email
        $responseEmail = $this->post('/login', [
            'username' => 'eka@politala.ac.id',
            'password' => 'password123',
        ]);
        $responseEmail->assertRedirect('/mahasiswa');
        $this->assertAuthenticatedAs($user);

        Auth::logout();

        // 2. Login menggunakan NIM huruf kecil
        $responseNimLower = $this->post('/login', [
            'username' => 'c030324005',
            'password' => 'password123',
        ]);
        $responseNimLower->assertRedirect('/mahasiswa');
        $this->assertAuthenticatedAs($user);

        Auth::logout();

        // 3. Login menggunakan NIM huruf kapital
        $responseNimUpper = $this->post('/login', [
            'username' => 'C030324005',
            'password' => 'password123',
        ]);
        $responseNimUpper->assertRedirect('/mahasiswa');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Google OAuth callback secara cerdas menghubungkan mahasiswa di Bank Data
     * tanpa membuat duplikat akun user atau record mahasiswa ganda.
     */
    public function test_google_oauth_links_existing_bank_data_student_without_duplicates(): void
    {
        $kelas = Kelas::create(['kelas' => 'TRKJ 3B', 'semester' => 3]);
        $bankMhs = Mahasiswa::create([
            'nim' => 'C030324006',
            'nama_mahasiswa' => 'Fajar Nugraha',
            'email_kampus' => 'fajar@politala.ac.id',
            'kelas_id' => $kelas->id,
            'user_id' => null,
            'semester' => 3,
        ]);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google_id_999888');
        $abstractUser->shouldReceive('getEmail')->andReturn('fajar@politala.ac.id');
        $abstractUser->shouldReceive('getName')->andReturn('Fajar Google');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/mahasiswa');
        $this->assertAuthenticated();

        // Pastikan hanya ada 1 user yang dibuat dan terhubung ke Bank Data
        $this->assertEquals(1, User::where('email', 'fajar@politala.ac.id')->count());
        $user = User::where('email', 'fajar@politala.ac.id')->first();

        $this->assertEquals('C030324006', $user->username);
        $this->assertEquals('Fajar Nugraha', $user->nama_user);
        $this->assertEquals('google_id_999888', $user->google_id);

        // Pastikan bank data terhubung dan tidak ada record mahasiswa ganda
        $bankMhs->refresh();
        $this->assertEquals($user->id, $bankMhs->user_id);
        $this->assertEquals(1, Mahasiswa::where('nim', 'C030324006')->count());
        $this->assertEquals(0, Mahasiswa::whereNull('nim')->count());
    }

    /**
     * Admin dicegah membuat user mahasiswa ganda jika NIM sudah terhubung ke akun lain.
     */
    public function test_admin_cannot_create_duplicate_user_for_already_claimed_student(): void
    {
        $admin = User::create([
            'nama_user' => 'Administrator Prodi',
            'username' => 'admin_prodi',
            'email' => 'admin_prodi@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_hp' => '081122334455',
        ]);

        $studentUser = User::create([
            'nama_user' => 'Gita Gutawa',
            'username' => 'C030324007',
            'email' => 'gita@politala.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567893',
        ]);

        Mahasiswa::create([
            'nim' => 'C030324007',
            'nama_mahasiswa' => 'Gita Gutawa',
            'user_id' => $studentUser->id,
            'semester' => 3,
        ]);

        $response = $this->actingAs($admin)->from('/admin/user/create')->post('/admin/user', [
            'nama_user' => 'Gita Baru',
            'username' => 'C030324007', // NIM yang sudah dipakai
            'email' => 'gita_baru@politala.ac.id',
            'no_hp' => '089912345678',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'mahasiswa',
        ]);

        // Karena username C030324007 sudah ada di users, validasi username unique akan gagal
        $response->assertRedirect('/admin/user/create');
        $response->assertSessionHasErrors('username');
    }

    /**
     * Halaman login menampilkan nilai isian dan banner petunjuk saat diakses dengan parameter login.
     */
    public function test_login_page_renders_prefilled_username_and_notice_banner(): void
    {
        $response = $this->get('/login?login=C030324001');

        $response->assertStatus(200);
        $response->assertSee('value="C030324001"', false);
        $response->assertSee('Akun Terdaftar Ditemukan!');
    }

    /**
     * Halaman registrasi menampilkan resolusi tindakan langsung jika ada akun terdaftar sebelumnya.
     */
    public function test_register_page_renders_resolution_card_with_direct_login_link(): void
    {
        $response = $this->withSession([
            'existing_user_resolution' => [
                'title' => 'Akun dengan NIM Ini Sudah Terdaftar!',
                'message' => 'NIM C030324001 sudah terdaftar atas nama Ahmad Santoso.',
                'login_key' => 'C030324001',
                'nama' => 'Ahmad Santoso',
            ],
        ])->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Akun dengan NIM Ini Sudah Terdaftar!');
        $response->assertSee('Ahmad Santoso');
        $response->assertSee(route('login', ['login' => 'C030324001']));
        $response->assertSee('Masuk Sekarang ke Akun Anda');
    }
}
