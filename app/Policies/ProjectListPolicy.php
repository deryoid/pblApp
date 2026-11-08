<?php

namespace App\Policies;

use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectListPolicy
{
    use HandlesAuthorization;

    protected function resolveContext(User $user): array
    {
        $mhs = Mahasiswa::where('user_id', $user->id)->first();
        if (! $mhs) {
            return [null, null];
        }

        $periodeAktif = Periode::getLatestActive();
        $kelompok = $periodeAktif
            ? $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first()
            : null;

        return [$kelompok, $periodeAktif];
    }

    public function create(User $user): bool
    {
        [$kelompok] = $this->resolveContext($user);

        return (bool) $kelompok;
    }

    public function update(User $user, ProjectList $list): bool
    {
        [$kelompok, $periodeAktif] = $this->resolveContext($user);
        if (! $kelompok) {
            return false;
        }
        $pid = $kelompok->periode_id ?? optional($periodeAktif)->id;

        return $list->kelompok_id === $kelompok->id && ($list->periode_id && $pid ? $list->periode_id === $pid : true);
    }

    public function delete(User $user, ProjectList $list): bool
    {
        return $this->update($user, $list);
    }
}
