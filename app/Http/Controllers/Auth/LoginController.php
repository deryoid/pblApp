<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class LoginController extends Controller
{
    /**
     * Tampilkan form login (jika sudah login → redirect sesuai role).
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return match ($user->role) {
                'admin' => redirect()->to('/admin'),
                'evaluator', 'dosen' => redirect()->to('/evaluator'),
                'mahasiswa' => redirect()->to('/mahasiswa'),
                default => redirect()->to('/'),
            };
        }

        return view('auth.login');
    }

    /**
     * Proses login (mendukung username, NIM, atau email).
     */
    public function login(Request $request): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'NIM, username, atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = (bool) $request->boolean('remember');
        $loginInput = trim((string) $request->input('username'));
        $password = (string) $request->input('password');

        // Deteksi apakah input berupa alamat email
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        $credentials = [
            $isEmail ? 'email' : 'username' => $isEmail ? strtolower($loginInput) : $loginInput,
            'password' => $password,
        ];

        // 1. Percobaan login primer
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            Alert::toast('Anda berhasil masuk.', 'success');

            $user = Auth::user();

            return match ($user->role) {
                'admin' => redirect()->to('/admin'),
                'evaluator', 'dosen' => redirect()->to('/evaluator'),
                'mahasiswa' => redirect()->to('/mahasiswa'),
                default => redirect()->intended('/'),
            };
        }

        // 2. Jika input berupa teks (bukan email) dan huruf kecil, coba dengan huruf kapital (format standar NIM)
        if (! $isEmail) {
            $uppercaseNIM = strtoupper($loginInput);
            if ($uppercaseNIM !== $loginInput && Auth::attempt(['username' => $uppercaseNIM, 'password' => $password], $remember)) {
                $request->session()->regenerate();

                Alert::toast('Anda berhasil masuk.', 'success');

                $user = Auth::user();

                return match ($user->role) {
                    'admin' => redirect()->to('/admin'),
                    'evaluator', 'dosen' => redirect()->to('/evaluator'),
                    'mahasiswa' => redirect()->to('/mahasiswa'),
                    default => redirect()->intended('/'),
                };
            }
        }

        // Gagal login
        Alert::toast('Username/Email atau kata sandi tidak cocok. Silakan periksa kembali.', 'error');

        return back()->withInput();
    }

    /**
     * Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Alert::toast('Anda berhasil keluar aplikasi.', 'success');

        return redirect()->to('/');
    }
}
