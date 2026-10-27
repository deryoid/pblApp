<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
     * Tangani callback dari Google OAuth dan lakukan proses autentikasi pengguna.
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

        $email = $googleUser->getEmail();
        if (empty($email)) {
            Alert::toast('Email tidak ditemukan dari akun Google Anda.', 'error');

            return redirect()->route('login');
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                ]);
            }
        }

        if (! $user) {
            $baseUsername = Str::slug(explode('@', $email)[0], '_') ?: 'user';
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername.'_'.$counter++;
            }

            $user = User::create([
                'nama_user' => $googleUser->getName() ?: $baseUsername,
                'email' => $email,
                'username' => $username,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
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
            if ($user->role === 'mahasiswa' && ! $user->mahasiswa()->exists()) {
                Mahasiswa::create([
                    'user_id' => $user->id,
                    'nama_mahasiswa' => $user->nama_user,
                    'semester' => 1,
                    'is_profile_complete' => false,
                ]);
            }
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        Alert::toast('Anda berhasil masuk dengan Google.', 'success');

        return match ($user->role) {
            'admin' => redirect()->to('/admin'),
            'evaluator', 'dosen' => redirect()->to('/evaluator'),
            'mahasiswa' => redirect()->to('/mahasiswa'),
            default => redirect()->intended('/'),
        };
    }
}
