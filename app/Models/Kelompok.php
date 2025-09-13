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
}
