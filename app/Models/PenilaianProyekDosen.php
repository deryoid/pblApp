<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PenilaianProyekDosen extends Model
{
    use HasFactory;

    protected $table = 'penilaian_proyek_dosen';

    protected $fillable = [
        'uuid',
        'project_card_id',
        'dosen_id',
        'mata_kuliah_id',
        'rubrik_kategori_id',
        'rubrik_indikator_id',
        'nilai',
        'catatan_umpan_balik',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function projectCard(): BelongsTo
    {
        return $this->belongsTo(ProjectCard::class, 'project_card_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(RubrikKategori::class, 'rubrik_kategori_id');
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(RubrikIndikator::class, 'rubrik_indikator_id');
    }
}
