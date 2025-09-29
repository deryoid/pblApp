<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EvaluasiMitra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluasi_mitra';

    protected $guarded = [];

    protected $casts = [
        'm_kehadiran' => 'integer',
        'm_presentasi' => 'integer',
        'rata_rata' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'tanggal_evaluasi' => 'datetime',
        'submitted_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->tanggal_evaluasi)) {
                $model->tanggal_evaluasi = now();
            }

            if (empty($model->evaluator_id) && Auth::check()) {
                $model->evaluator_id = Auth::id();
            }
        });

        static::saving(function (self $model) {
            $kriteria = ['m_kehadiran', 'm_presentasi'];

            $nilai = [];
            foreach ($kriteria as $key) {
                $value = $model->{$key};
                if ($value !== null) {
                    $nilai[$key] = (int) $value;
                }
            }

            $model->rata_rata = count($nilai) > 0 ? array_sum($nilai) / count($nilai) : null;

            if (! empty($nilai)) {
                $weights = [];

                try {
                    $weights = EvaluationRubricIndicator::whereIn('code', array_keys($nilai))
                        ->pluck('weight', 'code')
                        ->toArray();
                } catch (\Throwable $e) {
                    $weights = [];
                }

                if (empty($weights)) {
                    $settings = EvaluationSetting::whereIn('key', array_keys($nilai))
                        ->pluck('value', 'key')
                        ->toArray();

                    foreach ($settings as $key => $value) {
                        $weights[$key] = (int) $value;
                    }
                }

                if (empty($weights)) {
                    $defaultWeight = 100 / count($nilai);
                    foreach (array_keys($nilai) as $key) {
                        $weights[$key] = $defaultWeight;
                    }
                }

                $weighted = 0;
                foreach ($nilai as $key => $value) {
                    $weight = (int) ($weights[$key] ?? 0);
                    $weighted += ($value * $weight / 100);
                }

                $model->nilai_akhir = $weighted;
            } else {
                $model->nilai_akhir = null;
            }

            if ($model->isDirty('status')) {
                $newStatus = $model->status;
                $oldStatus = $model->getOriginal('status');

                if ($oldStatus === 'draft' && $newStatus === 'submitted') {
                    $model->submitted_at = now();
                }

                if ($newStatus === 'locked') {
                    $model->locked_at = now();
                    $model->locked_by = Auth::id();
                } elseif ($oldStatus === 'locked' && $newStatus !== 'locked') {
                    $model->locked_at = null;
                    $model->locked_by = null;
                }
            }
        });
    }

    protected static function masterKey(): string
    {
        static $column;

        if ($column) {
            return $column;
        }

        $instance = new static;
        $column = Schema::hasColumn($instance->getTable(), 'evaluasi_master_id')
            ? 'evaluasi_master_id'
            : 'evaluasi_sesi_id';

        return $column;
    }

    public function evaluasiMaster()
    {
        return $this->belongsTo(EvaluasiMaster::class, static::masterKey());
    }

    public function sesi()
    {
        return $this->evaluasiMaster();
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function projectCard()
    {
        return $this->belongsTo(ProjectCard::class, 'project_card_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function getGradeAttribute(): ?string
    {
        $score = $this->nilai_akhir;

        if ($score === null) {
            return null;
        }

        if ($score >= 85) {
            return 'A';
        }
        if ($score >= 75) {
            return 'B';
        }
        if ($score >= 65) {
            return 'C';
        }
        if ($score >= 55) {
            return 'D';
        }

        return 'E';
    }

    public function getIsCompleteAttribute(): bool
    {
        return $this->m_kehadiran !== null && $this->m_presentasi !== null;
    }
}
