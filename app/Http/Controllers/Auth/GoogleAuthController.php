<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use RealRashid\SweetAlert\Facades\Alert;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect pengguna ke halaman autentikasi Google OAuth.
     */
    public function redirect(): RedirectResponse
    {
        if (! class_exists(Socialite::class)) {
            Alert::toast('Paket Socialite belum terpasang di server. Jalankan "composer install" di server.', 'error');

            return redirect()->route('login');
        }

        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            Alert::toast('Kredensial Google OAuth (Client ID / Secret) belum dikonfigurasi di server (.env).', 'warning');

            return redirect()->route('login');
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (Throwable $e) {
            Alert::toast('Gagal melakukan redirect ke Google: '.$e->getMessage(), 'error');

            return redirect()->route('login');
        }
    }

    /**
     * Tangani callback dari Google OAuth dan lakukan proses autentikasi pengguna secara cerdas tanpa duplikasi akun.
     */
    public function callback(): RedirectResponse
    {
        if (! class_exists(Socialite::class)) {
            Alert::toast('Paket Socialite belum terpasang di server. Jalankan "composer install" di server.', 'error');

            return redirect()->route('login');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Alert::toast('Gagal melakukan autentikasi dengan Google: '.$e->getMessage(), 'error');

            return redirect()->route('login');
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));
        if (empty($email)) {
            Alert::toast('Email tidak ditemukan dari akun Google Anda.', 'error');

            return redirect()->route('login');
        }

        $googleId = (string) $googleUser->getId();
        $avatar = $googleUser->getAvatar();
        $googleName = $googleUser->getName();

        // 1. Cari pengguna berdasarkan google_id
        $user = User::where('google_id', $googleId)->first();

        // 2. Jika tidak ditemukan by google_id, cari by email di users
        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleId,
                    'avatar' => $user->avatar ?: $avatar,
                ]);
            }
        }

        // 3. Jika user belum ada di tabel users, CEK Bank Data Mahasiswa & Dosen agar TIDAK DOUBLE!
        if (! $user) {
            $emailPrefix = strtoupper(explode('@', $email)[0]);

            // Cek apakah mahasiswa ini sudah ada di Bank Data Mahasiswa (by email atau NIM)
            $bankMhs = Mahasiswa::where('email_kampus', $email)
                ->orWhere('nim', $emailPrefix)
                ->first();

            if ($bankMhs) {
                // Jika data bank mahasiswa ditemukan:
                if ($bankMhs->user_id) {
                    $user = User::find($bankMhs->user_id);
                    if ($user) {
                        $user->update([
                            'google_id' => $googleId,
                            'avatar' => $user->avatar ?: $avatar,
                            'email' => $user->email ?: $email,
                        ]);
                    }
                }

                if (! $user) {
                    // Buat akun User baru yang langsung ditautkan ke Bank Data Mahasiswa (bukan dummy/duplikat!)
                    $user = User::create([
                        'nama_user' => $bankMhs->nama_mahasiswa,
                        'email' => $email,
                        'username' => $bankMhs->nim,
                        'google_id' => $googleId,
                        'avatar' => $avatar,
                        'role' => 'mahasiswa',
                        'email_verified_at' => now(),
                    ]);

                    $bankMhs->user_id = $user->id;
                    $bankMhs->email_kampus = $bankMhs->email_kampus ?: $email;
                    $bankMhs->checkAndUpdateProfileCompletion();
                    $bankMhs->save();
                }
            } else {
                // Cek apakah email terdaftar sebagai Dosen
                $dosenUser = User::where('email', $email)->where('role', 'dosen')->first();
                if ($dosenUser) {
                    $user = $dosenUser;
                    $user->update([
                        'google_id' => $googleId,
                        'avatar' => $user->avatar ?: $avatar,
                    ]);
                } else {
                    // Cek apakah ada di Bank Data Dosen
                    $bankDosen = \App\Models\Dosen::where('nip', $emailPrefix)
                        ->orWhere('nama_dosen', 'LIKE', '%'.$googleName.'%')
                        ->first();

                    if ($bankDosen) {
                        $user = User::create([
                            'nama_user' => $bankDosen->nama_dosen ?: ($googleName ?: $emailPrefix),
                            'email' => $email,
                            'username' => ! empty($bankDosen->nip) ? $bankDosen->nip : $emailPrefix,
                            'google_id' => $googleId,
                            'avatar' => $avatar,
                            'role' => 'dosen',
                            'email_verified_at' => now(),
                        ]);

                        $bankDosen->user_id = $user->id;
                        $bankDosen->save();
                    }
                }
            }
        }

        // 4. Jika tetap belum ada user (pengguna mandiri baru):
        if (! $user) {
            $baseUsername = Str::slug(explode('@', $email)[0], '_') ?: 'user';
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername.'_'.$counter++;
            }

            $user = User::create([
                'nama_user' => $googleName ?: $baseUsername,
                'email' => $email,
                'username' => $username,
                'google_id' => $googleId,
                'avatar' => $avatar,
                'role' => 'mahasiswa',
                'email_verified_at' => now(),
            ]);

            Mahasiswa::create([
                'user_id' => $user->id,
                'nama_mahasiswa' => $user->nama_user,
                'semester' => 1,
                'is_profile_complete' => false,
            ]);
        } else {
            $user->syncAndFixRole();

            // Pastikan relasi mahasiswa sinkron jika role mahasiswa
            if ($user->role === 'mahasiswa' && ! $user->mahasiswa()->exists()) {
                // Cek apakah ada record di Bank Data sebelum membuat record baru
                $bankMhs = Mahasiswa::where('nim', strtoupper($user->username))
                    ->orWhere('email_kampus', $user->email)
                    ->first();

                if ($bankMhs) {
                    $bankMhs->user_id = $user->id;
                    $bankMhs->checkAndUpdateProfileCompletion();
                    $bankMhs->save();
                } else {
                    Mahasiswa::create([
                        'user_id' => $user->id,
                        'nama_mahasiswa' => $user->nama_user,
                        'semester' => 1,
                        'is_profile_complete' => false,
                    ]);
                }
            }
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        Alert::toast('Anda berhasil masuk dengan Google.', 'success');

        return match ($user->role) {
            'admin' => redirect()->to('/admin'),
            'dosen', 'evaluator' => redirect()->to('/dosen'),
            'mahasiswa' => redirect()->to('/mahasiswa'),
            default => redirect()->intended('/'),
        };
    }
}
