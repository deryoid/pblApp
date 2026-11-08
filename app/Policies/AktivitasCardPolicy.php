<?php

namespace App\Policies;

use App\Models\AktivitasCard;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
        $mhs = Mahasiswa::where('user_id', $user->id)->first();
        if (! $mhs) {
            return false;
        }

        $periodeAktif = Periode::getLatestActive();
        $kelompok = $periodeAktif
            ? $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first()
            : null;

        return (bool) ($kelompok ?? $mhs->kelompoks()->first());
    }

    public function update(User $user, AktivitasCard $card): bool
    {
        $mhs = Mahasiswa::where('user_id', $user->id)->first();
        if (! $mhs) {
            return false;
        }

        // Mahasiswa diizinkan mengelola kartu aktivitas jika merupakan anggota kelompok pemilik kartu tersebut
        return $mhs->kelompoks()->where('kelompok.id', $card->kelompok_id)->exists();
    }

    public function delete(User $user, AktivitasCard $card): bool
    {
        return $this->update($user, $card);
    }

    public function view(User $user, AktivitasCard $card): bool
    {
        return $this->update($user, $card);
    }
}
