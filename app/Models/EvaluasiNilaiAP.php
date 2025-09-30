<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EvaluasiNilaiAP extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluasi_nilai_ap';

    protected $guarded = ['id'];

    protected $casts = [
        'w_ap_presentasi' => 'decimal:2',
        'tanggal_hadir' => 'date',
    ];

    // Kehadiran values dengan bobotnya
    public const KEHADIRAN_VALUES = [
        'Hadir' => 100,
        'Dispensasi' => 70,
        'Terlambat' => 50,
        'Sakit' => 60,
        'Tanpa Keterangan' => 0,
        'Tidak Hadir' => 0, // Alias untuk Tanpa Keterangan
        'Izin' => 70, // Alias untuk Dispensasi
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class);
    }

    public function evaluasiMaster()
    {
        return $this->belongsTo(EvaluasiMaster::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function aktivitasList()
    {
        return $this->belongsTo(AktivitasList::class, 'aktivitas_list_id');
    }

    // Accessors
    public function getKehadiranBobotAttribute(): float
    {
        return self::KEHADIRAN_VALUES[$this->w_ap_kehadiran] ?? 0;
    }

    public function getKehadiranBobotFormattedAttribute(): string
    {
        return number_format($this->kehadiran_bobot, 0);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'Draft' => '<span class="badge badge-secondary">Draft</span>',
            'Submitted' => '<span class="badge badge-primary">Submitted</span>',
            'Reviewed' => '<span class="badge badge-success">Reviewed</span>',
            default => '<span class="badge badge-light">Unknown</span>',
        };
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // Scopes
    public function scopeByPeriode($query, $periodeId)
    {
        return $query->where('periode_id', $periodeId);
    }

    public function scopeByKelompok($query, $kelompokId)
    {
        return $query->where('kelompok_id', $kelompokId);
    }

    public function scopeByMahasiswa($query, $mahasiswaId)
    {
        return $query->where('mahasiswa_id', $mahasiswaId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'Submitted');
    }
}
