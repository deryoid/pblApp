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

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
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

    /**
     * Update or create a record safely, auto-resolving legacy unique constraint collisions if present.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public static function safeUpdateOrCreate(array $attributes, array $values = []): self
    {
        try {
            return static::updateOrCreate($attributes, $values);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'evaluasi_nilai_ap_unique')) {
                static::dropLegacyUniqueIndex();

                return static::updateOrCreate($attributes, $values);
            }

            throw $e;
        }
    }

    /**
     * Safely drop the legacy unique index if still present in database.
     */
    public static function dropLegacyUniqueIndex(): void
    {
        try {
            $driver = \Illuminate\Support\Facades\DB::getDriverName();

            if ($driver === 'mysql') {
                $existingMasterIndexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM evaluasi_nilai_ap WHERE Column_name = 'evaluasi_master_id' AND Key_name != 'evaluasi_nilai_ap_unique'"));
                if ($existingMasterIndexes->isEmpty()) {
                    \Illuminate\Support\Facades\DB::statement('ALTER TABLE evaluasi_nilai_ap ADD INDEX evaluasi_nilai_ap_evaluasi_master_id_idx (evaluasi_master_id)');
                }

                \Illuminate\Support\Facades\DB::statement('ALTER TABLE evaluasi_nilai_ap DROP INDEX evaluasi_nilai_ap_unique');
            } elseif ($driver === 'sqlite') {
                \Illuminate\Support\Facades\DB::statement('DROP INDEX IF EXISTS evaluasi_nilai_ap_unique');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to drop legacy evaluasi_nilai_ap_unique index: '.$e->getMessage());
        }
    }
}
