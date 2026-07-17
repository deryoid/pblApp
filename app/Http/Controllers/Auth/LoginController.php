<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * Tampilkan form login (jika sudah login → redirect sesuai role).
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();

            return match ($user->role) {
                'admin'     => redirect()->to('/admin'),
                'evaluator' => redirect()->to('/evaluator'),
                'mahasiswa' => redirect()->to('/mahasiswa'),
                default     => redirect()->to('/'),
            };
        }

        return view('auth.login');
    }

    /**
     * Proses login (username + password).
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'username' => ['required','string'],
            'password' => ['required','string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        // Siapkan kredensial
        $credentials = $request->only('username', 'password');

        // Coba login
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->role === 'mahasiswa') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Alert::error('Anda tidak bisa login, Periode Penilaian Sudah Selesai', '"Dan janganlah kamu iri hati terhadap apa yang dikaruniakan Allah kepada sebagian kamu lebih banyak dari sebagian yang lain.  : An-Nisa ayat 32"')->autoClose(5000);
                
                return back()->withInput();
            }

            $request->session()->regenerate();

            // Toast sukses
            Alert::toast('Anda berhasil masuk.', 'success');

            $user = Auth::user();

            // Redirect berdasarkan role
            return match ($user->role) {
                'admin'     => redirect()->to('/admin'),
                'evaluator' => redirect()->to('/evaluator'),
                'mahasiswa' => redirect()->to('/mahasiswa'),
                default     => redirect()->intended('/'),
            };
        }

        // Gagal login
        Alert::toast('Username atau password salah.', 'error');
        return back()->withInput(); // kembalikan input username
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Alert::toast('Anda berhasil keluar aplikasi.', 'success');

        return redirect()->to('/');
    }
}
