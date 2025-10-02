<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Kelompok extends Model
{
    protected $table = 'kelompok';

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    // Route model binding pakai UUID
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function mahasiswas(): BelongsToMany
    {
        return $this->belongsToMany(Mahasiswa::class, 'kelompok_mahasiswa')
            ->withPivot(['periode_id', 'kelas_id', 'role'])
            ->withTimestamps();
    }

    // Ketua dari pivot (role = 'Ketua' / 'ketua')
    public function ketuaFromPivot(): BelongsToMany
    {
        return $this->mahasiswas()
            ->where(function ($q) {
                $q->wherePivot('role', 'Ketua')->orWherePivot('role', 'ketua');
            });
    }

    // Accessor object ketua (tanpa nambah query kalau sudah eager-loaded)
    public function getKetuaAttribute()
    {
        if ($this->relationLoaded('mahasiswas')) {
            return $this->mahasiswas->first(function ($m) {
                $r = strtolower((string) ($m->pivot->role ?? ''));

                return $r === 'ketua';
            });
        }

        return $this->ketuaFromPivot()->first();
    }

    // Accessor nama ketua (support kolom nama atau nama_mahasiswa)
    public function getKetuaNamaAttribute(): ?string
    {
        $m = $this->ketua;

        return $m->nama ?? $m->nama_mahasiswa ?? null;
    }

    public function aktivitasLists()
    {
        return $this->hasMany(AktivitasList::class);
    }

    public function evaluasiMasters()
    {
        return $this->hasMany(EvaluasiMaster::class);
    }

    // Check evaluation status for the group based on aktivitas_list
    public function getEvaluationStatusAttribute(): string
    {
        // Check if group has any evaluated activities
        $hasEvaluatedActivities = $this->aktivitasLists()
            ->where('status_evaluasi', 'Sudah Evaluasi')
            ->exists();

        return $hasEvaluatedActivities ? 'Sudah Evaluasi' : 'Belum Evaluasi';
    }

    // Get evaluation status badge class for styling
    public function getEvaluationStatusBadgeAttribute(): string
    {
        return $this->evaluation_status === 'Sudah Evaluasi' ? 'badge-success' : 'badge-secondary';
    }

    // Get total activities count for the group
    public function getTotalActivitiesAttribute(): int
    {
        return $this->aktivitasLists()->count();
    }

    // Get evaluated activities count for the group
    public function getEvaluatedActivitiesCountAttribute(): int
    {
        return $this->aktivitasLists()
            ->where('status_evaluasi', 'Sudah Evaluasi')
            ->count();
    }

    // Get unevaluated activities count for the group
    public function getUnevaluatedActivitiesCountAttribute(): int
    {
        return $this->aktivitasLists()
            ->where('status_evaluasi', 'Belum Evaluasi')
            ->count();
    }

    // Get activity boxes for display (green for evaluated, black for unevaluated)
    public function getActivityBoxesAttribute(): string
    {
        $evaluated = $this->evaluated_activities_count;
        $unevaluated = $this->unevaluated_activities_count;

        $boxes = '';

        // Green boxes for evaluated activities
        for ($i = 0; $i < $evaluated; $i++) {
            $boxes .= '<span class="activity-box evaluated" title="Sudah Evaluasi"></span>';
        }

        // Black boxes for unevaluated activities
        for ($i = 0; $i < $unevaluated; $i++) {
            $boxes .= '<span class="activity-box unevaluated" title="Belum Evaluasi"></span>';
        }

        return $boxes;
    }
}
