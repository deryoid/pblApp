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
            'email' => ['required', 'email', 'max:255'],
            'no_hp' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
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

        // 2. Cek apakah NIM ini sudah memiliki akun pengguna aktif (di bank data atau di users)
        $existingUserByNim = $bankMhs->user_id
            ? User::find($bankMhs->user_id)
            : User::where('username', $nim)->first();

        if ($existingUserByNim || $bankMhs->user_id !== null) {
            $userFound = $existingUserByNim ?: User::where('username', $nim)->first();
            $loginKey = $userFound?->username ?: $nim;

            $resolution = [
                'title' => 'Akun dengan NIM Ini Sudah Terdaftar!',
                'message' => 'NIM '.$nim.' ('.$bankMhs->nama_mahasiswa.') sudah memiliki akun pengguna aktif di sistem PBL. Anda tidak perlu mendaftar ulang. Silakan langsung masuk menggunakan NIM atau email Anda.',
                'login_key' => $loginKey,
                'email' => $userFound?->email,
                'nama' => $bankMhs->nama_mahasiswa,
            ];

            Alert::info(
                'Akun Sudah Terdaftar',
                'NIM '.$nim.' sudah terdaftar atas nama '.$bankMhs->nama_mahasiswa.'. Silakan masuk langsung dengan akun Anda.'
            )->autoClose(8000);

            return back()->withInput()->with('existing_user_resolution', $resolution)->withErrors([
                'nim' => 'NIM '.$nim.' sudah terhubung dengan akun pengguna aktif ('.$bankMhs->nama_mahasiswa.'). Silakan masuk melalui halaman login.',
            ]);
        }

        // 3. Cek apakah alamat email sudah digunakan di tabel users
        $existingUserByEmail = User::where('email', $email)->first();

        if ($existingUserByEmail) {
            // Skenario A: Auto-link Google OAuth / Unlinked Student
            // Hanya berlaku jika akun dibuat via Google OAuth dan belum memiliki NIM resmi di Bank Data
            $userMahasiswa = $existingUserByEmail->mahasiswa;
            $canAutoLink = ($existingUserByEmail->role === 'mahasiswa' && $bankMhs->user_id === null)
                && (
                    $existingUserByEmail->google_id !== null
                    && (empty($userMahasiswa) || empty($userMahasiswa->nim))
                );

            if ($canAutoLink) {
                DB::transaction(function () use ($existingUserByEmail, $bankMhs, $nim, $email, $noHp, $request) {
                    // Hapus record mahasiswa dummy tanpa NIM yang mungkin dibuat saat Google login sebelumnya
                    if ($existingUserByEmail->mahasiswa && $existingUserByEmail->mahasiswa->id !== $bankMhs->id && empty($existingUserByEmail->mahasiswa->nim)) {
                        $existingUserByEmail->mahasiswa->delete();
                    }

                    $existingUserByEmail->update([
                        'nama_user' => $bankMhs->nama_mahasiswa,
                        'username' => $nim,
                        'no_hp' => $noHp,
                        'password' => Hash::make($request->password),
                        'role' => 'mahasiswa',
                        'email_verified_at' => $existingUserByEmail->email_verified_at ?: now(),
                    ]);

                    $bankMhs->user_id = $existingUserByEmail->id;
                    $bankMhs->email_kampus = $bankMhs->email_kampus ?: $email;
                    $bankMhs->is_profile_complete = ! empty($bankMhs->kelas_id);
                    $bankMhs->save();
                });

                Auth::login($existingUserByEmail);
                $request->session()->regenerate();

                Alert::success(
                    'Sinkronisasi Akun Berhasil!',
                    'Akun Anda dengan email '.$email.' berhasil disinkronkan dan ditautkan dengan Bank Data Mahasiswa (NIM '.$nim.'). Selamat datang, '.$bankMhs->nama_mahasiswa.'!'
                )->autoClose(7000);

                return redirect()->to('/mahasiswa');
            }

            // Skenario B: Email sudah terdaftar pada akun lain yang sudah aktif
            $resolution = [
                'title' => 'Email Ini Sudah Terdaftar!',
                'message' => 'Alamat email '.$email.' sudah terdaftar pada akun dengan username "'.$existingUserByEmail->username.'" ('.$existingUserByEmail->nama_user.'). Jika ini akun Anda, silakan masuk ke akun tersebut.',
                'login_key' => $existingUserByEmail->username,
                'email' => $email,
                'nama' => $existingUserByEmail->nama_user,
            ];

            Alert::warning(
                'Email Sudah Terdaftar',
                'Email '.$email.' sudah terdaftar pada akun '.$existingUserByEmail->username.'. Silakan gunakan akun tersebut untuk masuk.'
            )->autoClose(8000);

            return back()->withInput()->with('existing_user_resolution', $resolution)->withErrors([
                'email' => 'Email ini sudah terdaftar pada akun '.$existingUserByEmail->username.'. Silakan login dengan akun tersebut atau gunakan email aktif lainnya.',
            ]);
        }

        // 4. Cek apakah username NIM sudah dipakai di tabel users
        if (User::where('username', $nim)->exists()) {
            $userByNimUsername = User::where('username', $nim)->first();
            $resolution = [
                'title' => 'Username NIM Sudah Digunakan!',
                'message' => 'Username dengan NIM '.$nim.' sudah ada di sistem. Silakan login ke akun Anda.',
                'login_key' => $nim,
                'email' => $userByNimUsername?->email,
                'nama' => $userByNimUsername?->nama_user,
            ];

            return back()->withInput()->with('existing_user_resolution', $resolution)->withErrors([
                'nim' => 'Akun dengan username NIM '.$nim.' sudah ada di sistem. Silakan login atau hubungi administrator.',
            ]);
        }

        // 5. Buat akun User baru dan tautkan ke Bank Data Mahasiswa
        $user = DB::transaction(function () use ($bankMhs, $nim, $email, $noHp, $request) {
            $newUser = User::create([
                'nama_user' => $bankMhs->nama_mahasiswa,
                'username' => $nim,
                'email' => $email,
                'no_hp' => $noHp,
                'password' => Hash::make($request->password),
                'role' => 'mahasiswa',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            // Tautkan data user ke record Bank Data Mahasiswa
            $bankMhs->user_id = $newUser->id;
            $bankMhs->email_kampus = $bankMhs->email_kampus ?: $email;
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
