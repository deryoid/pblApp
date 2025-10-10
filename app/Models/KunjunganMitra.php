<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class KunjunganMitra extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_mitra';
    protected $guarded = ['id','uuid'];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->uuid)) $m->uuid = (string) Str::uuid();
        });

        static::saved(function (self $m) {
            if ($m->user_id) {
                Cache::forget('mahasiswa_dashboard_' . $m->user_id);
            }
        });

        static::deleted(function (self $m) {
            if ($m->user_id) {
                Cache::forget('mahasiswa_dashboard_' . $m->user_id);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function periode(): BelongsTo { return $this->belongsTo(Periode::class); }
    public function kelompok(): BelongsTo { return $this->belongsTo(Kelompok::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getBuktiDataUrlAttribute(): ?string
    {
        if (!$this->bukti_kunjungan || !$this->bukti_kunjungan_mime) return null;
        return 'data:'.$this->bukti_kunjungan_mime.';base64,'.base64_encode($this->bukti_kunjungan);
    }
}

