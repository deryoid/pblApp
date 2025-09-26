<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluasiProyekNilai extends Model
{
    protected $table = 'evaluasi_proyek_nilai';
    protected $guarded = ['id'];

    protected $casts = [
        'nilai' => 'array',
        'total' => 'integer',
    ];

    public function card(){ return $this->belongsTo(ProjectCard::class, 'card_id'); }
    public function sesi(){ return $this->belongsTo(EvaluasiSesi::class, 'sesi_id'); }
}

