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

    protected function resolveKelompokId(User $user): ?int
    {
        $mhs = Mahasiswa::where('user_id', $user->id)->first();
        if (! $mhs) {
            return null;
        }

        $periodeAktif = Periode::getLatestActive();
        if (! $periodeAktif) {
            return null;
        }

        $kelompok = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();

        return $kelompok?->id;
    }

    public function create(User $user): bool
    {
        return (bool) $this->resolveKelompokId($user);
    }

    public function update(User $user, AktivitasCard $card): bool
    {
        $kelompokId = $this->resolveKelompokId($user);

        return $kelompokId !== null && $card->kelompok_id === $kelompokId;
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
