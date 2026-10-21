<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RubrikIndikator extends Model
{
    use HasFactory;

    protected $table = 'rubrik_indikator';

    protected $fillable = [
        'uuid',
        'kategori_id',
        'kode',
        'nama_indikator',
        'bobot',
        'skala_min',
        'skala_max',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'decimal:2',
            'skala_min' => 'decimal:2',
            'skala_max' => 'decimal:2',
            'urutan' => 'integer',
            'is_active' => 'boolean',
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

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(RubrikKategori::class, 'kategori_id');
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(PenilaianProyekDosen::class, 'rubrik_indikator_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }
}
