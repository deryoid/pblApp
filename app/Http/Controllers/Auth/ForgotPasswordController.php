<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;
use Throwable;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan formulir permintaan kode reset kata sandi.
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirimkan kode verifikasi 6 digit ke email pengguna.
     */
    public function sendResetCode(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
        ], [
            'login.required' => 'Silakan masukkan alamat email atau NIM akun Anda.',
        ]);

        $input = trim((string) $request->input('login'));
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $user = User::where('email', strtolower($input))->first();
        } else {
            $upperInput = strtoupper(preg_replace('/\s+/', '', $input));
            $user = User::where('username', $input)
                ->orWhere('username', $upperInput)
                ->orWhereHas('mahasiswa', fn ($q) => $q->where('nim', $upperInput))
                ->first();
        }

        if (! $user) {
            return back()->withInput()->withErrors([
                'login' => 'Akun dengan email atau NIM tersebut tidak ditemukan dalam sistem.',
            ]);
        }

        if (empty($user->email)) {
            return back()->withInput()->withErrors([
                'login' => 'Akun Anda belum memiliki alamat email yang terdaftar. Silakan hubungi Administrator Prodi TRKJ.',
            ]);
        }

        // Generate 6 digit angka acak
        $code = (string) random_int(100000, 999999);

        // Simpan token ke tabel password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        // Kirim email
        try {
            Mail::to($user->email)->send(new ResetPasswordCodeMail($user, $code));
        } catch (Throwable $e) {
            Log::error('Gagal mengirim email reset password: '.$e->getMessage());
        }

        $masked = $this->maskEmail($user->email);

        Alert::success(
            'Kode Verifikasi Terkirim!',
            'Kode verifikasi 6 digit telah dikirimkan ke email '.$masked.'. Silakan periksa kotak masuk atau spam.'
        )->autoClose(8000);

        return redirect()->route('password.reset', ['email' => $user->email]);
    }

    /**
     * Tampilkan formulir verifikasi kode dan pengisian kata sandi baru.
     */
    public function showResetForm(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email', old('email', '')),
        ]);
    }

    /**
     * Verifikasi kode 6 digit dan perbarui kata sandi pengguna.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar di sistem.',
            'code.required' => 'Kode verifikasi 6 digit wajib diisi.',
            'code.size' => 'Kode verifikasi harus terdiri dari 6 digit.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $code = trim((string) $request->input('code'));

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record) {
            return back()->withInput()->withErrors([
                'code' => 'Permintaan reset kata sandi tidak ditemukan atau telah kedaluwarsa. Silakan minta kode baru.',
            ]);
        }

        // Cek kedaluwarsa (15 menit)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return back()->withInput()->withErrors([
                'code' => 'Kode verifikasi telah kedaluwarsa (berlaku 15 menit). Silakan minta kode baru.',
            ]);
        }

        // Cek kecocokan kode verifikasi
        if (! Hash::check($code, $record->token) && $code !== $record->token) {
            return back()->withInput()->withErrors([
                'code' => 'Kode verifikasi 6 digit yang Anda masukkan tidak cocok. Silakan periksa kembali email Anda.',
            ]);
        }

        // Perbarui kata sandi pengguna
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withInput()->withErrors([
                'email' => 'Pengguna tidak ditemukan.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus token yang sudah berhasil digunakan
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        Alert::success(
            'Kata Sandi Berhasil Diperbarui!',
            'Kata sandi akun Anda berhasil diperbarui. Silakan masuk menggunakan kata sandi baru Anda.'
        )->autoClose(7000);

        return redirect()->route('login', ['login' => $user->username]);
    }

    /**
     * Kirim ulang kode verifikasi ke email pengguna.
     */
    public function resendCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.exists' => 'Email tidak terdaftar di sistem.',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $user = User::where('email', $email)->firstOrFail();

        // Generate 6 digit angka acak
        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        try {
            Mail::to($user->email)->send(new ResetPasswordCodeMail($user, $code));
        } catch (Throwable $e) {
            Log::error('Gagal mengirim ulang email reset password: '.$e->getMessage());
        }

        $masked = $this->maskEmail($user->email);

        Alert::success(
            'Kode Baru Terkirim!',
            'Kode verifikasi 6 digit baru telah dikirimkan ke email '.$masked.'. Silakan periksa kotak masuk atau spam.'
        )->autoClose(8000);

        return redirect()->route('password.reset', ['email' => $user->email]);
    }

    /**
     * Samarkan karakter alamat email untuk keamanan tampilan.
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);

        if ($len <= 2) {
            $maskedName = substr($name, 0, 1).'*';
        } else {
            $maskedName = substr($name, 0, 2).str_repeat('*', max(1, $len - 3)).substr($name, -1);
        }

        return $maskedName.'@'.$domain;
    }
}
