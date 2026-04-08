<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nim',
        'nama_mahasiswa',
        'user_id',
        'kelas_id',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->uuid)) {
                $m->uuid = (string) Str::uuid(); // isi internal, bukan dari request
            }
        });

        static::updated(function ($m) {
            if ($m->user_id) {
                Cache::forget('mahasiswa_dashboard_'.$m->user_id);
            }
        });

        static::deleted(function ($m) {
            if ($m->user_id) {
                Cache::forget('mahasiswa_dashboard_'.$m->user_id);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function kelompoks(): BelongsToMany
    {
        return $this->belongsToMany(Kelompok::class, 'kelompok_mahasiswa')
            ->withPivot(['periode_id', 'role'])
            ->withTimestamps();
    }

    public function nilaiAP(): HasMany
    {
        return $this->hasMany(EvaluasiNilaiAP::class, 'mahasiswa_id');
    }
}
