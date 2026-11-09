<?php

namespace App\Policies;

use App\Models\Mahasiswa;
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
        $mhs = $this->resolveMahasiswa($user);
        if (! $mhs) {
            return false;
        }

        return DB::table('kelompok_mahasiswa')->where('mahasiswa_id', $mhs->id)->exists();
    }

    public function update(User $user, ProjectList $list): bool
    {
        $mhs = $this->resolveMahasiswa($user);
        if (! $mhs) {
            return false;
        }

        // Mahasiswa diizinkan mengelola list/kolom jika merupakan anggota kelompok pemilik kolom tersebut
        return DB::table('kelompok_mahasiswa')
            ->where('mahasiswa_id', $mhs->id)
            ->where('kelompok_id', $list->kelompok_id)
            ->exists();
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
        return $user->mahasiswa
            ?? Mahasiswa::where('user_id', $user->id)->first()
            ?? Mahasiswa::where('nim', $user->username)->first();
    }
}
