<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Periode extends Model
{
    use HasFactory;

    protected $table = 'periode';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'periode',
        'status_periode',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public static function getLatestActive(): ?self
    {
        return self::where('status_periode', 'Aktif')->orderByDesc('id')->first()
            ?? self::orderByDesc('id')->first();
    }

    public function kelompoks(): HasMany
    {
        return $this->hasMany(Kelompok::class);
    }
}
