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

    protected static function booted() {
        static::creating(fn($m) => $m->uuid ??= (string) Str::uuid());
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
            ->withPivot(['periode_id','kelas_id','role'])
            ->withTimestamps();
    }

    // Ketua dari pivot (role = 'Ketua' / 'ketua')
    public function ketuaFromPivot(): BelongsToMany
    {
        return $this->mahasiswas()
            ->where(function($q){
                $q->wherePivot('role','Ketua')->orWherePivot('role','ketua');
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
}
