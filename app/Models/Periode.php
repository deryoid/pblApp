<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Periode extends Model
{
    use HasFactory;

    protected $table = 'periode';
    protected $guarded = ['id','uuid'];   // semua kolom boleh diisi mass assignment

    public function getRouteKeyName(): string 
    { 
        return 'uuid'; // biar route model binding pakai uuid
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
    public function kelompoks()
    { 
        return $this->hasMany(Kelompok::class); 
    }

    
}