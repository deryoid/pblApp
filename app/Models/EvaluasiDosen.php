<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EvaluasiDosen extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $table = 'evaluasi_dosen';

    protected $guarded = [];

    protected $casts = [
        'd_hasil' => 'integer',
        'd_teknis' => 'integer',
        'd_user' => 'integer',
        'd_efisiensi' => 'integer',
        'd_dokpro' => 'integer',
        'd_inisiatif' => 'integer',
        'rata_rata' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'tanggal_evaluasi' => 'datetime',
        'submitted_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    // Boot method untuk generate UUID dan auto-calculation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = \Illuminate\Support\Str::uuid();
            }

            if (empty($model->tanggal_evaluasi)) {
                $model->tanggal_evaluasi = now();
            }

            if (empty($model->evaluator_id)) {
                $model->evaluator_id = Auth::check() ? Auth::id() : null;
            }
        });

        static::saving(function ($model) {
            // Hitung rata-rata
            $kriteria = ['d_hasil', 'd_teknis', 'd_user', 'd_efisiensi', 'd_dokpro', 'd_inisiatif'];
            $values = [];
            $count = 0;

            foreach ($kriteria as $k) {
                if ($model->$k !== null && $model->$k > 0) {
                    $values[] = $model->$k;
                    $count++;
                }
            }

            $model->rata_rata = $count > 0 ? array_sum($values) / $count : null;

            // Hitung nilai akhir dengan pembobotan
            if ($count > 0) {
                // Coba dapatkan bobot dari evaluation_rubric_indicators dulu
                try {
                    $indicators = \App\Models\EvaluationRubricIndicator::whereIn('code', [
                        'd_hasil', 'd_teknis', 'd_user', 'd_efisiensi', 'd_dokpro', 'd_inisiatif',
                    ])->pluck('weight', 'code')->toArray();
                } catch (\Exception $e) {
                    $indicators = [];
                }

                // Fallback ke evaluation_settings
                $settings = \App\Models\EvaluationSetting::whereIn('key', [
                    'd_hasil', 'd_teknis', 'd_user', 'd_efisiensi', 'd_dokpro', 'd_inisiatif',
                ])->pluck('value', 'key')->toArray();

                $weightedTotal = 0;
                foreach ($kriteria as $k) {
                    if ($model->$k !== null && $model->$k > 0) {
                        // Prioritaskan bobot dari indicators, fallback ke settings, default ke bobot rata-rata
                        $weight = $indicators[$k] ?? $settings[$k] ?? (100 / count($kriteria));
                        $weightedTotal += ($model->$k * $weight / 100);
                    }
                }

                $model->nilai_akhir = $weightedTotal;
            }

            // Handle status changes
            if ($model->isDirty('status')) {
                $newStatus = $model->status;
                $oldStatus = $model->getOriginal('status');

                if ($oldStatus === 'draft' && $newStatus === 'submitted') {
                    $model->submitted_at = now();
                }

                if ($newStatus === 'locked') {
                    $model->locked_at = now();
                    $model->locked_by = Auth::check() ? Auth::id() : null;
                } elseif ($oldStatus === 'locked' && $newStatus !== 'locked') {
                    $model->locked_at = null;
                    $model->locked_by = null;
                }
            }
        });
    }

    // Relationships
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

    // Accessor untuk grade (dihitung saat dibutuhkan)
    public function getGradeAttribute()
    {
        if ($this->nilai_akhir === null) {
            return null;
        }

        if ($this->nilai_akhir >= 85) {
            return 'A';
        }
        if ($this->nilai_akhir >= 75) {
            return 'B';
        }
        if ($this->nilai_akhir >= 65) {
            return 'C';
        }
        if ($this->nilai_akhir >= 55) {
            return 'D';
        }

        return 'E';
    }

    // Accessor untuk status label
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'locked' => 'Locked',
            default => ucfirst($this->status)
        };
    }

    // Accessor untuk evaluation type label
    public function getEvaluationTypeLabelAttribute()
    {
        return match ($this->evaluation_type) {
            'regular' => 'Regular',
            'remedial' => 'Remedial',
            'improvement' => 'Improvement',
            default => ucfirst($this->evaluation_type)
        };
    }

    // Helper method untuk cek apakah bisa di-edit
    public function getIsEditableAttribute()
    {
        return $this->status !== 'locked';
    }

    // Helper method untuk cek apakah bisa di-submit
    public function getCanBeSubmittedAttribute()
    {
        return $this->status === 'draft' && $this->is_complete;
    }

    // Helper method untuk cek apakah bisa dikunci
    public function getCanBeLockedAttribute()
    {
        return $this->status === 'submitted';
    }

    // Scope queries
    public function scopeByEvaluasiMaster($query, $masterId)
    {
        return $query->where(static::masterKey(), $masterId);
    }

    public function scopeBySesi($query, $masterId)
    {
        return $this->scopeByEvaluasiMaster($query, $masterId);
    }

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

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_card_id', $projectId);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('nilai_akhir');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    public function scopeByEvaluationType($query, $type)
    {
        return $query->where('evaluation_type', $type);
    }

    public function scopeByEvaluator($query, $evaluatorId)
    {
        return $query->where('evaluator_id', $evaluatorId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_evaluasi', [$startDate, $endDate]);
    }

    public function scopeHighScore($query, $threshold = 80)
    {
        return $query->where('nilai_akhir', '>=', $threshold);
    }

    public function scopeLowScore($query, $threshold = 60)
    {
        return $query->where('nilai_akhir', '<', $threshold);
    }

    // Get all kriteria dengan nilai
    public function getKriteriaAttribute()
    {
        return [
            'd_hasil' => $this->d_hasil,
            'd_teknis' => $this->d_teknis,
            'd_user' => $this->d_user,
            'd_efisiensi' => $this->d_efisiensi,
            'd_dokpro' => $this->d_dokpro,
            'd_inisiatif' => $this->d_inisiatif,
        ];
    }

    // Check if all kriteria have been filled
    public function getIsCompleteAttribute()
    {
        $kriteria = ['d_hasil', 'd_teknis', 'd_user', 'd_efisiensi', 'd_dokpro', 'd_inisiatif'];
        foreach ($kriteria as $k) {
            if ($this->$k === null) {
                return false;
            }
        }

        return true;
    }

    // Get kriteria descriptions
    public function getKriteriaDescriptionsAttribute()
    {
        return [
            'd_hasil' => 'Kualitas Hasil Proyek',
            'd_teknis' => 'Tingkat Kompleksitas Teknis',
            'd_user' => 'Kesesuaian dengan Kebutuhan Pengguna',
            'd_efisiensi' => 'Efisiensi Waktu dan Biaya',
            'd_dokpro' => 'Dokumentasi dan Profesionalisme',
            'd_inisiatif' => 'Kemandirian dan Inisiatif',
        ];
    }

    // Get validation rules
    public static function getValidationRules()
    {
        return [
            'evaluasi_master_id' => 'required|exists:evaluasi_master,id',
            'periode_id' => 'required|exists:periode,id',
            'kelompok_id' => 'required|exists:kelompok,id',
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'project_card_id' => 'required|exists:project_cards,id',
            'evaluator_id' => 'nullable|exists:users,id',
            'd_hasil' => 'nullable|integer|min:0|max:100',
            'd_teknis' => 'nullable|integer|min:0|max:100',
            'd_user' => 'nullable|integer|min:0|max:100',
            'd_efisiensi' => 'nullable|integer|min:0|max:100',
            'd_dokpro' => 'nullable|integer|min:0|max:100',
            'd_inisiatif' => 'nullable|integer|min:0|max:100',
            'tanggal_evaluasi' => 'nullable|date',
            'status' => 'required|in:draft,submitted,locked',
            'catatan' => 'nullable|string|max:1000',
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'evaluation_type' => 'required|in:regular,remedial,improvement',
        ];
    }
}
