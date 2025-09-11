<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';
    protected $guarded = ['id','uuid'];   // semua kolom boleh diisi mass assignment

 
    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->uuid)) {
                $m->uuid = (string) Str::uuid(); // isi internal, bukan dari request
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
