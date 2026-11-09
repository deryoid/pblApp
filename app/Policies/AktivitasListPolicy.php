<?php

namespace App\Policies;

use App\Models\AktivitasList;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;

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
        $mhs = $this->resolveMahasiswa($user);
        if (! $mhs) {
            return false;
        }

        return DB::table('kelompok_mahasiswa')->where('mahasiswa_id', $mhs->id)->exists();
    }

    public function update(User $user, AktivitasList $list): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $mhs = $this->resolveMahasiswa($user);

        // 1. Cek langsung ke pivot kelompok_mahasiswa
        if ($mhs && DB::table('kelompok_mahasiswa')
            ->where('kelompok_id', $list->kelompok_id)
            ->where(function ($q) use ($mhs, $user) {
                $q->where('mahasiswa_id', $mhs->id)
                    ->orWhere('mahasiswa_id', $user->id);
            })->exists()) {
            return true;
        }

        // 2. Cek lewat relasi mahasiswas pada kelompok list
        $kelompok = Kelompok::find($list->kelompok_id);
        if ($kelompok) {
            $memberUserIds = $kelompok->mahasiswas()->pluck('user_id')->filter()->all();
            if (in_array($user->id, $memberUserIds, true)) {
                return true;
            }
            if ($mhs) {
                $memberMhsIds = $kelompok->mahasiswas()->pluck('mahasiswa.id')->filter()->all();
                if (in_array($mhs->id, $memberMhsIds, true)) {
                    return true;
                }
            }
        }

        // 3. Cek kelompok aktif mahasiswa
        $periodeAktif = Periode::getLatestActive();
        if ($mhs && $periodeAktif) {
            $kelompokAktif = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();
            if ($kelompokAktif && (int) $kelompokAktif->id === (int) $list->kelompok_id) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, AktivitasList $list): bool
    {
        return $this->update($user, $list);
    }

    public function view(User $user, AktivitasList $list): bool
    {
        return $this->update($user, $list);
    }

    protected function resolveMahasiswa(User $user): ?Mahasiswa
    {
        return $user->mahasiswa
            ?? Mahasiswa::where('user_id', $user->id)->first()
            ?? Mahasiswa::where('nim', $user->username)->first()
            ?? Mahasiswa::where('email_kampus', $user->email)->first();
    }
}
