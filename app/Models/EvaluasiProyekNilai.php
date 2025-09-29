<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EvaluasiProyekNilai extends Model
{
    protected $table = 'evaluasi_proyek_nilai';

    protected $guarded = ['id'];

    protected $casts = [
        'nilai' => 'array',
        'total' => 'integer',
    ];

    public function card()
    {
        return $this->belongsTo(ProjectCard::class, 'card_id');
    }

    public function evaluasiMaster()
    {
        $foreignKey = Schema::hasColumn($this->getTable(), 'evaluasi_master_id') ? 'evaluasi_master_id' : 'sesi_id';

        return $this->belongsTo(EvaluasiMaster::class, $foreignKey);
    }

    public function sesi()
    {
        return $this->evaluasiMaster();
    }
}
