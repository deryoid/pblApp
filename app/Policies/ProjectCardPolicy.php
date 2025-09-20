<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectCardPolicy
{
    use HandlesAuthorization;

    protected function resolveKelompokId(User $user): ?int
    {
        $mhs = Mahasiswa::where('user_id', $user->id)->first();
        if (!$mhs) return null;
        $periodeAktif = Periode::where('status_periode','Aktif')->orderByDesc('created_at')->first();
        $kelompok = null;
        if ($periodeAktif) {
            $kelompok = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();
        }
        if (!$kelompok) {
            $kelompok = $mhs->kelompoks()->latest('kelompok_mahasiswa.created_at')->first();
        }
        return $kelompok?->id;
    }

    public function create(User $user): bool
    {
        return (bool) $this->resolveKelompokId($user);
    }

    public function update(User $user, ProjectCard $card): bool
    {
        $kelompokId = $this->resolveKelompokId($user);
        return $kelompokId !== null && $card->kelompok_id === $kelompokId;
    }

    public function delete(User $user, ProjectCard $card): bool
    {
        return $this->update($user, $card);
    }

    public function view(User $user, ProjectCard $card): bool
    {
        return $this->update($user, $card);
    }
}

