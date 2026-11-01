<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubrikSkala extends Model
{
    use HasFactory;

    protected $table = 'rubrik_skala';

    protected $fillable = [
        'rubrik_kategori_id',
        'nilai_huruf',
        'rentang_nilai',
        'nilai_min',
        'nilai_max',
        'deskripsi',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'nilai_min' => 'decimal:2',
            'nilai_max' => 'decimal:2',
            'urutan' => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(RubrikKategori::class, 'rubrik_kategori_id');
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->nilai_huruf) {
            'A' => 'badge-success',
            'B+' => 'badge-primary',
            'B' => 'badge-info',
            'C+' => 'badge-warning',
            'C' => 'badge-warning',
            'D+' => 'badge-danger',
            'D' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
