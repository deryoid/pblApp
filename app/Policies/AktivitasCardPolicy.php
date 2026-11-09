<?php

namespace App\Policies;

use App\Models\AktivitasCard;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;

class AktivitasCardPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        $mhs = $this->resolveMahasiswa($user);
        if (! $mhs) {
            return false;
        }

        return DB::table('kelompok_mahasiswa')->where('mahasiswa_id', $mhs->id)->exists();
    }

    public function update(User $user, AktivitasCard $card): bool
    {
        $mhs = $this->resolveMahasiswa($user);
        if (! $mhs) {
            return false;
        }

        // Mahasiswa diizinkan mengelola kartu aktivitas jika merupakan anggota kelompok pemilik kartu tersebut
        return DB::table('kelompok_mahasiswa')
            ->where('mahasiswa_id', $mhs->id)
            ->where('kelompok_id', $card->kelompok_id)
            ->exists();
    }

    public function delete(User $user, AktivitasCard $card): bool
    {
        return $this->update($user, $card);
    }

    public function view(User $user, AktivitasCard $card): bool
    {
        return $this->update($user, $card);
    }

    protected function resolveMahasiswa(User $user): ?Mahasiswa
    {
        return $user->mahasiswa
            ?? Mahasiswa::where('user_id', $user->id)->first()
            ?? Mahasiswa::where('nim', $user->username)->first();
    }
}
