<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordCodeMail;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordWithEmailCodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'nama_user' => 'Mahasiswa Tester',
            'username' => 'mhs_tester',
            'email' => 'mhs.tester@politala.ac.id',
            'password' => Hash::make('oldpassword123'),
            'role' => 'mahasiswa',
            'no_hp' => '081234567899',
        ]);
    }

    /**
     * Halaman lupa kata sandi dapat diakses.
     */
    public function test_forgot_password_page_is_accessible(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Lupa Kata Sandi?');
        $response->assertSee('Kirim Kode Verifikasi');
    }

    /**
     * Halaman login memiliki tautan ke lupa kata sandi.
     */
    public function test_login_page_contains_forgot_password_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee(route('password.request'));
        $response->assertSee('Lupa kata sandi?');
    }

    /**
     * Mengirim kode verifikasi via email yang terdaftar.
     */
    public function test_send_reset_code_with_valid_email(): void
    {
        Mail::fake();

        $response = $this->post(route('password.email'), [
            'login' => 'mhs.tester@politala.ac.id',
        ]);

        $response->assertRedirect(route('password.reset', ['email' => 'mhs.tester@politala.ac.id']));

        // Cek email dikirimkan
        Mail::assertSent(ResetPasswordCodeMail::class, function ($mail) {
            return $mail->hasTo('mhs.tester@politala.ac.id') &&
                strlen($mail->code) === 6 &&
                ctype_digit($mail->code);
        });

        // Cek record di tabel password_reset_tokens
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'mhs.tester@politala.ac.id',
        ]);
    }

    /**
     * Mengirim kode verifikasi menggunakan NIM mahasiswa.
     */
    public function test_send_reset_code_with_student_nim(): void
    {
        Mail::fake();

        $kelas = Kelas::create([
            'kelas' => 'TRKJ 1A',
            'semester' => 1,
        ]);

        Mahasiswa::create([
            'user_id' => $this->user->id,
            'kelas_id' => $kelas->id,
            'nim' => 'C030324999',
            'nama_mahasiswa' => 'Mahasiswa Tester',
        ]);

        $response = $this->post(route('password.email'), [
            'login' => 'C030324999',
        ]);

        $response->assertRedirect(route('password.reset', ['email' => 'mhs.tester@politala.ac.id']));

        Mail::assertSent(ResetPasswordCodeMail::class, function ($mail) {
            return $mail->hasTo('mhs.tester@politala.ac.id') &&
                strlen($mail->code) === 6;
        });
    }

    /**
     * Gagal mengirim kode jika akun tidak ditemukan.
     */
    public function test_send_reset_code_with_unknown_account_fails(): void
    {
        Mail::fake();

        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'login' => 'tidakada@politala.ac.id',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHasErrors('login');

        Mail::assertNothingSent();
    }

    /**
     * Halaman form reset kata sandi dapat diakses dan email terisi otomatis.
     */
    public function test_reset_password_page_is_accessible(): void
    {
        $response = $this->get(route('password.reset', ['email' => 'mhs.tester@politala.ac.id']));

        $response->assertStatus(200);
        $response->assertSee('Atur Sandi Baru');
        $response->assertSee('mhs.tester@politala.ac.id');
        $response->assertSee('Perbarui Kata Sandi');
    }

    /**
     * Berhasil memperbarui kata sandi dengan kode 6 digit yang valid.
     */
    public function test_reset_password_succeeds_with_valid_code(): void
    {
        $code = '482915';

        DB::table('password_reset_tokens')->insert([
            'email' => $this->user->email,
            'token' => Hash::make($code),
            'created_at' => now(),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $this->user->email,
            'code' => $code,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login', ['login' => $this->user->username]));

        // Kata sandi terbarui di database
        $this->assertTrue(Hash::check('newpassword123', $this->user->fresh()->password));

        // Token sudah dihapus
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $this->user->email,
        ]);
    }

    /**
     * Gagal memperbarui kata sandi jika kode salah.
     */
    public function test_reset_password_fails_with_invalid_code(): void
    {
        $validCode = '482915';
        $wrongCode = '111111';

        DB::table('password_reset_tokens')->insert([
            'email' => $this->user->email,
            'token' => Hash::make($validCode),
            'created_at' => now(),
        ]);

        $response = $this->from(route('password.reset', ['email' => $this->user->email]))->post(route('password.update'), [
            'email' => $this->user->email,
            'code' => $wrongCode,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('password.reset', ['email' => $this->user->email]));
        $response->assertSessionHasErrors('code');

        // Kata sandi lama tetap tidak berubah
        $this->assertTrue(Hash::check('oldpassword123', $this->user->fresh()->password));
    }

    /**
     * Gagal memperbarui kata sandi jika kode sudah kedaluwarsa (> 15 menit).
     */
    public function test_reset_password_fails_when_code_is_expired(): void
    {
        $code = '482915';

        DB::table('password_reset_tokens')->insert([
            'email' => $this->user->email,
            'token' => Hash::make($code),
            'created_at' => Carbon::now()->subMinutes(16),
        ]);

        $response = $this->from(route('password.reset', ['email' => $this->user->email]))->post(route('password.update'), [
            'email' => $this->user->email,
            'code' => $code,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('password.reset', ['email' => $this->user->email]));
        $response->assertSessionHasErrors('code');

        // Token expired dihapus dari tabel
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $this->user->email,
        ]);

        // Kata sandi tidak berubah
        $this->assertTrue(Hash::check('oldpassword123', $this->user->fresh()->password));
    }

    /**
     * Berhasil mengirim ulang kode verifikasi ke email.
     */
    public function test_resend_code_sends_new_email_and_updates_token(): void
    {
        Mail::fake();

        $response = $this->post(route('password.resend'), [
            'email' => $this->user->email,
        ]);

        $response->assertRedirect(route('password.reset', ['email' => $this->user->email]));

        Mail::assertSent(ResetPasswordCodeMail::class, function ($mail) {
            return $mail->hasTo($this->user->email) &&
                strlen($mail->code) === 6;
        });

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $this->user->email,
        ]);
    }
}
