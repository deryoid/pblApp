<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluasiIndikator extends Model
{
    protected $table = 'evaluasi_indikator';

    protected $guarded = ['id'];

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'tipe',
        'bobot',
        'aktif',
    ];
}
