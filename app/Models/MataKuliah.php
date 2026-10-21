<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'uuid',
        'kode_matkul',
        'nama_matkul',
        'semester',
        'sks',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'semester' => 'integer',
            'sks' => 'integer',
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

    public function projectCards(): BelongsToMany
    {
        return $this->belongsToMany(ProjectCard::class, 'project_mata_kuliah')
            ->withTimestamps();
    }

    public function dosenPenilai(): HasMany
    {
        return $this->hasMany(ProjectDosenPenilai::class, 'mata_kuliah_id');
    }

    public function penilaianProyek(): HasMany
    {
        return $this->hasMany(PenilaianProyekDosen::class, 'mata_kuliah_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSemester($query, int $semester)
    {
        return $query->where('semester', $semester);
    }
}
