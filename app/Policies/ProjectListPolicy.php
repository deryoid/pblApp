<?php

namespace App\Policies;

use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\ProjectCard;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;

class ProjectListPolicy
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
        if ($user->role === 'admin') {
            return true;
        }

        $mhs = $this->resolveMahasiswa($user);

        return DB::table('kelompok_mahasiswa')
            ->where(function ($q) use ($mhs, $user) {
                $q->where('mahasiswa_id', $user->id);
                if ($mhs) {
                    $q->orWhere('mahasiswa_id', $mhs->id);
                }
            })->exists();
    }

    public function update(User $user, ProjectList $list): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $mhs = $this->resolveMahasiswa($user);

        // 1. Cek langsung user_id atau mhs_id ke pivot kelompok_mahasiswa
        if (DB::table('kelompok_mahasiswa')
            ->where('kelompok_id', $list->kelompok_id)
            ->where(function ($q) use ($mhs, $user) {
                $q->where('mahasiswa_id', $user->id);
                if ($mhs) {
                    $q->orWhere('mahasiswa_id', $mhs->id);
                }
            })->exists()) {
            return true;
        }

        // 2. Cek apakah user pernah membuat kartu di list ini atau di kelompok ini
        if ($list->cards()->where('created_by', $user->id)->exists()
            || ProjectCard::where('kelompok_id', $list->kelompok_id)->where('created_by', $user->id)->exists()) {
            return true;
        }

        // 3. Cek lewat relasi mahasiswas pada kelompok list
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

        // 4. Cek kelompok aktif atau kelompok apapun milik mahasiswa
        if ($mhs) {
            $periodeAktif = Periode::getLatestActive();
            if ($periodeAktif) {
                $kelompokAktif = $mhs->kelompoks()->wherePivot('periode_id', $periodeAktif->id)->first();
                if ($kelompokAktif && (int) $kelompokAktif->id === (int) $list->kelompok_id) {
                    return true;
                }
            }

            if ($mhs->kelompoks()->where('kelompok.id', $list->kelompok_id)->exists()) {
                return true;
            }
        }

        // 5. Fallback kelompok pengguna mahasiswa
        if ($user->role === 'mahasiswa') {
            $userKelompokIds = DB::table('kelompok_mahasiswa')
                ->where(function ($q) use ($user, $mhs) {
                    $q->where('mahasiswa_id', $user->id);
                    if ($mhs) {
                        $q->orWhere('mahasiswa_id', $mhs->id);
                    }
                })
                ->pluck('kelompok_id')
                ->all();

            if (in_array($list->kelompok_id, $userKelompokIds, false)) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, ProjectList $list): bool
    {
        return $this->update($user, $list);
    }

    public function view(User $user, ProjectList $list): bool
    {
        return $this->update($user, $list);
    }

    protected function resolveMahasiswa(User $user): ?Mahasiswa
    {
        $mhs = $user->mahasiswa
            ?? Mahasiswa::where('user_id', $user->id)->first()
            ?? Mahasiswa::whereRaw('LOWER(TRIM(nim)) = LOWER(TRIM(?))', [$user->username])->first()
            ?? Mahasiswa::where('email_kampus', $user->email)->first()
            ?? ($user->email ? Mahasiswa::where('email_kampus', $user->email)->orWhere('user_id', $user->id)->first() : null)
            ?? ($user->no_hp ? Mahasiswa::where('no_hp', $user->no_hp)->first() : null)
            ?? Mahasiswa::whereRaw('LOWER(TRIM(nama_mahasiswa)) = LOWER(TRIM(?))', [$user->nama_user])->first();

        // Auto-link user_id ke record mahasiswa jika belum terhubung
        if ($mhs && ! $mhs->user_id) {
            $mhs->update(['user_id' => $user->id]);
        }

        return $mhs;
    }
}
