<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AktivitasList extends Model
{
    use HasFactory;

    protected $table = 'aktivitas_lists';

    protected $guarded = ['id'];

    protected $casts = [
        'rentang_tanggal' => 'string',
        'status_evaluasi' => 'string',
        'w_ap_kehadiran' => 'decimal:2',
        'w_ap_presentasi' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $list) {
            if (empty($list->uuid)) {
                $list->uuid = (string) Str::uuid();
            }
            if ($list->position === null) {
                $list->position = 0;
            }
            if (empty($list->status_evaluasi)) {
                $list->status_evaluasi = 'Belum Evaluasi';
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function cards()
    {
        return $this->hasMany(AktivitasCard::class, 'list_aktivitas_id')
            ->orderBy('position');
    }

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function presensi()
    {
        return $this->hasMany(AktivitasPresensi::class);
    }

    public function getBobotAPAttribute()
    {
        return [
            'kehadiran' => $this->w_ap_kehadiran ?? 50,
            'presentasi' => $this->w_ap_presentasi ?? 50,
        ];
    }
}
