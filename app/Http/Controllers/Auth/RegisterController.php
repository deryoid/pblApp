<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class RegisterController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran khusus mahasiswa.
     * Role lain (admin, dosen, evaluator) didaftarkan oleh administrator.
     */
    public function showRegistrationForm(): View|RedirectResponse
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

        return view('auth.register');
    }

    /**
     * Proses pendaftaran mahasiswa dengan identifikasi NIM terhadap Bank Data Mahasiswa.
     */
    public function register(Request $request): RedirectResponse
    {
        // Normalisasi input
        $nim = strtoupper(preg_replace('/\s+/', '', (string) $request->input('nim')));
        $email = strtolower(trim((string) $request->input('email')));
        $noHp = preg_replace('/\s+/', '', trim((string) $request->input('no_hp')));

        $request->merge([
            'nim' => $nim,
            'email' => $email,
            'no_hp' => $noHp,
        ]);

        $request->validate([
            'nim' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar pada akun lain.',
            'no_hp.required' => 'Nomor HP / WhatsApp wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // 1. Identifikasi NIM pada Bank Data Mahasiswa
        $bankMhs = Mahasiswa::where('nim', $nim)->first();

        if (! $bankMhs) {
            return back()->withInput()->withErrors([
                'nim' => 'NIM '.$nim.' tidak ditemukan dalam Bank Data Mahasiswa. Silakan hubungi Administrator Program Studi TRKJ untuk didaftarkan.',
            ]);
        }

        // 2. Cek apakah NIM ini sudah memiliki akun pengguna
        if ($bankMhs->user_id !== null) {
            return back()->withInput()->withErrors([
                'nim' => 'NIM '.$nim.' sudah terhubung dengan akun pengguna aktif. Silakan masuk melalui halaman login.',
            ]);
        }

        // 3. Cek apakah username NIM sudah dipakai di tabel users
        if (User::where('username', $nim)->exists()) {
            return back()->withInput()->withErrors([
                'nim' => 'Akun dengan username NIM ini sudah ada di sistem. Silakan login atau hubungi administrator.',
            ]);
        }

        // 4. Buat akun User dan tautkan ke Bank Data Mahasiswa
        $user = DB::transaction(function () use ($bankMhs, $nim, $email, $noHp, $request) {
            $newUser = User::create([
                'nama_user' => $bankMhs->nama_mahasiswa, // Sesuaikan dengan nama resmi di Bank Data Mahasiswa
                'username' => $nim,
                'email' => $email,
                'no_hp' => $noHp,
                'password' => Hash::make($request->password),
                'role' => 'mahasiswa', // Pendaftaran mandiri dikhususkan untuk role mahasiswa
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            // Tautkan data user ke record Bank Data Mahasiswa
            $bankMhs->user_id = $newUser->id;
            $bankMhs->is_profile_complete = ! empty($bankMhs->kelas_id);
            $bankMhs->save();

            return $newUser;
        });

        // Login otomatis setelah registrasi berhasil
        Auth::login($user);
        $request->session()->regenerate();

        Alert::success(
            'Pendaftaran Berhasil!',
            'Selamat datang, '.$bankMhs->nama_mahasiswa.'! Akun Anda berhasil teridentifikasi dan disinkronkan dengan Bank Data Mahasiswa.'
        )->autoClose(6000);

        return redirect()->to('/mahasiswa');
    }
}
