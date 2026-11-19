<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
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
     * Tampilkan formulir pendaftaran (Mahasiswa / Dosen).
     */
    public function showRegistrationForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return match ($user->role) {
                'admin' => redirect()->to('/admin'),
                'dosen', 'evaluator' => redirect()->to('/dosen'),
                'mahasiswa' => redirect()->to('/mahasiswa'),
                default => redirect()->to('/'),
            };
        }

        $activeRole = session('active_role', $request->query('role', 'mahasiswa'));
        if (! in_array($activeRole, ['mahasiswa', 'dosen'], true)) {
            $activeRole = 'mahasiswa';
        }

        return view('auth.register', compact('activeRole'));
    }

    /**
     * Tampilkan formulir pendaftaran khusus Dosen.
     */
    public function showDosenRegistrationForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return match ($user->role) {
                'admin' => redirect()->to('/admin'),
                'dosen', 'evaluator' => redirect()->to('/dosen'),
                'mahasiswa' => redirect()->to('/mahasiswa'),
                default => redirect()->to('/'),
            };
        }

        $activeRole = 'dosen';

        return view('auth.register', compact('activeRole'));
    }

    /**
     * Proses pendaftaran mahasiswa dengan identifikasi NIM terhadap Bank Data Mahasiswa.
     */
    public function register(Request $request): RedirectResponse
    {
        if ($request->input('role') === 'dosen') {
            return $this->registerDosen($request);
        }

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
            return back()->withInput()->with('active_role', 'mahasiswa')->withErrors([
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

            return back()->withInput()->with('active_role', 'mahasiswa')->with('existing_user_resolution', $resolution)->withErrors([
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

            return back()->withInput()->with('active_role', 'mahasiswa')->with('existing_user_resolution', $resolution)->withErrors([
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

            return back()->withInput()->with('active_role', 'mahasiswa')->with('existing_user_resolution', $resolution)->withErrors([
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

    /**
     * Proses pendaftaran dosen dengan sinkronisasi terhadap Bank Data Dosen.
     */
    public function registerDosen(Request $request): RedirectResponse
    {
        // Normalisasi input
        $nip = strtoupper(preg_replace('/\s+/', '', (string) $request->input('nip')));
        $namaUser = preg_replace('/\s+/', ' ', trim((string) $request->input('nama_user')));
        $email = strtolower(trim((string) $request->input('email')));
        $noHp = preg_replace('/\s+/', '', trim((string) $request->input('no_hp')));
        $keahlian = trim((string) $request->input('keahlian'));

        $request->merge([
            'nip' => $nip,
            'nama_user' => $namaUser,
            'email' => $email,
            'no_hp' => $noHp,
            'keahlian' => $keahlian ?: null,
        ]);

        $request->validate([
            'nip' => ['required', 'string', 'max:50'],
            'nama_user' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'no_hp' => ['required', 'string', 'max:30'],
            'keahlian' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'nip.required' => 'NIP / NIDN wajib diisi.',
            'nama_user.required' => 'Nama lengkap dan gelar dosen wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'no_hp.required' => 'Nomor WhatsApp / HP aktif wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // 1. Identifikasi NIP pada Bank Data Dosen
        $bankDosen = Dosen::where('nip', $nip)->first();

        // 2. Cek apakah NIP ini sudah memiliki akun pengguna aktif
        $existingUserByNip = $bankDosen?->user_id
            ? User::find($bankDosen->user_id)
            : User::where('username', $nip)->first();

        if ($existingUserByNip || ($bankDosen && $bankDosen->user_id !== null)) {
            $userFound = $existingUserByNip ?: User::where('username', $nip)->first();
            $loginKey = $userFound?->username ?: $nip;

            $resolution = [
                'title' => 'Akun Dosen dengan NIP Ini Sudah Terdaftar!',
                'message' => 'NIP '.$nip.' ('.($bankDosen?->nama_dosen ?: $userFound?->nama_user).') sudah memiliki akun pengguna aktif di sistem PBL. Anda tidak perlu mendaftar ulang. Silakan langsung masuk menggunakan NIP atau email Anda.',
                'login_key' => $loginKey,
                'email' => $userFound?->email,
                'nama' => $bankDosen?->nama_dosen ?: $userFound?->nama_user,
            ];

            Alert::info(
                'Akun Sudah Terdaftar',
                'NIP '.$nip.' sudah terdaftar atas nama '.($bankDosen?->nama_dosen ?: $userFound?->nama_user).'. Silakan masuk langsung dengan akun Anda.'
            )->autoClose(8000);

            return back()->withInput()->with('active_role', 'dosen')->with('existing_user_resolution', $resolution)->withErrors([
                'nip' => 'NIP '.$nip.' sudah terhubung dengan akun pengguna aktif. Silakan masuk melalui halaman login.',
            ]);
        }

        // 3. Cek apakah alamat email sudah digunakan di tabel users
        $existingUserByEmail = User::where('email', $email)->first();

        if ($existingUserByEmail) {
            // Skenario A: Auto-link Google OAuth untuk Dosen
            $canAutoLink = ($existingUserByEmail->google_id !== null)
                && ($existingUserByEmail->role === 'dosen')
                && ($bankDosen === null || $bankDosen->user_id === null)
                && (! $existingUserByEmail->dosen || empty($existingUserByEmail->dosen->nip));

            if ($canAutoLink) {
                DB::transaction(function () use ($existingUserByEmail, $bankDosen, $nip, $namaUser, $noHp, $keahlian, $request) {
                    $existingUserByEmail->update([
                        'nama_user' => $namaUser ?: $existingUserByEmail->nama_user,
                        'username' => $nip,
                        'no_hp' => $noHp,
                        'password' => Hash::make($request->password),
                        'role' => 'dosen',
                        'email_verified_at' => $existingUserByEmail->email_verified_at ?: now(),
                    ]);

                    if ($bankDosen) {
                        $bankDosen->user_id = $existingUserByEmail->id;
                        $bankDosen->nama_dosen = $namaUser ?: $bankDosen->nama_dosen;
                        $bankDosen->no_hp = $noHp;
                        if ($keahlian) {
                            $bankDosen->keahlian = $keahlian;
                        }
                        $bankDosen->save();
                    } else {
                        Dosen::create([
                            'user_id' => $existingUserByEmail->id,
                            'nip' => $nip,
                            'nama_dosen' => $namaUser,
                            'no_hp' => $noHp,
                            'keahlian' => $keahlian ?: null,
                        ]);
                    }
                });

                Auth::login($existingUserByEmail);
                $request->session()->regenerate();

                Alert::success(
                    'Sinkronisasi Akun Berhasil!',
                    'Akun Anda dengan email '.$email.' berhasil ditautkan dengan Bank Data Dosen (NIP '.$nip.'). Selamat datang, '.$namaUser.'!'
                )->autoClose(7000);

                return redirect()->to('/dosen');
            }

            // Skenario B: Email sudah terdaftar pada akun lain
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

            return back()->withInput()->with('active_role', 'dosen')->with('existing_user_resolution', $resolution)->withErrors([
                'email' => 'Email ini sudah terdaftar pada akun '.$existingUserByEmail->username.'. Silakan login dengan akun tersebut atau gunakan email aktif lainnya.',
            ]);
        }

        // 4. Cek apakah username NIP sudah dipakai di tabel users
        if (User::where('username', $nip)->exists()) {
            $userByNipUsername = User::where('username', $nip)->first();
            $resolution = [
                'title' => 'Username NIP Sudah Digunakan!',
                'message' => 'Username dengan NIP '.$nip.' sudah ada di sistem. Silakan login ke akun Anda.',
                'login_key' => $nip,
                'email' => $userByNipUsername?->email,
                'nama' => $userByNipUsername?->nama_user,
            ];

            return back()->withInput()->with('active_role', 'dosen')->with('existing_user_resolution', $resolution)->withErrors([
                'nip' => 'Akun dengan username NIP '.$nip.' sudah ada di sistem. Silakan login atau hubungi administrator.',
            ]);
        }

        // 5. Buat akun User Dosen baru dan tautkan ke Bank Data Dosen (atau buat record Bank Data Dosen baru jika belum ada)
        $user = DB::transaction(function () use ($bankDosen, $nip, $namaUser, $email, $noHp, $keahlian, $request) {
            $newUser = User::create([
                'nama_user' => $namaUser ?: ($bankDosen?->nama_dosen ?? 'Dosen'),
                'username' => $nip,
                'email' => $email,
                'no_hp' => $noHp,
                'password' => Hash::make($request->password),
                'role' => 'dosen',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            if ($bankDosen) {
                // Tautkan data user ke record Bank Data Dosen yang sudah ada
                $bankDosen->user_id = $newUser->id;
                $bankDosen->nama_dosen = $newUser->nama_user;
                if (empty($bankDosen->no_hp)) {
                    $bankDosen->no_hp = $noHp;
                }
                if ($keahlian) {
                    $bankDosen->keahlian = $keahlian;
                }
                $bankDosen->save();
            } else {
                // Buat record Bank Data Dosen baru
                Dosen::create([
                    'user_id' => $newUser->id,
                    'nip' => $nip,
                    'nama_dosen' => $newUser->nama_user,
                    'no_hp' => $noHp,
                    'keahlian' => $keahlian ?: null,
                ]);
            }

            return $newUser;
        });

        // Login otomatis setelah registrasi berhasil
        Auth::login($user);
        $request->session()->regenerate();

        Alert::success(
            'Pendaftaran Dosen Berhasil!',
            'Selamat datang, '.$user->nama_user.'! Akun Dosen Anda berhasil didaftarkan dan disinkronkan ke dalam sistem PBL.'
        )->autoClose(6000);

        return redirect()->to('/dosen');
    }
}
