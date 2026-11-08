<?php

namespace App\Policies;

use App\Models\AktivitasList;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AktivitasListPolicy
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

    public function update(User $user, AktivitasList $list): bool
    {
        $mhs = Mahasiswa::where('user_id', $user->id)->first();
        if (! $mhs) {
            return false;
        }

        // Mahasiswa diizinkan mengelola kolom aktivitas jika merupakan anggota kelompok pemilik kolom tersebut
        return $mhs->kelompoks()->where('kelompok.id', $list->kelompok_id)->exists();
    }

    public function delete(User $user, AktivitasList $list): bool
    {
        return $this->update($user, $list);
    }

    public function view(User $user, AktivitasList $list): bool
    {
        return $this->update($user, $list);
    }
}
