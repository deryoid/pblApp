<?php

// app/Models/EvaluasiSesiIndikator.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiSesiIndikator extends Model
{
    protected $table = 'evaluasi_sesi_indikator';

    protected $guarded = ['id'];

    protected $casts = [
        'bobot' => 'integer',
        'urutan' => 'integer',
        'skor' => 'integer',
    ];

    public function evaluasiMaster(): BelongsTo
    {
        return $this->belongsTo(EvaluasiMaster::class, 'sesi_id');
    }

    public function sesi(): BelongsTo
    {
        return $this->evaluasiMaster();
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(EvaluasiIndikator::class, 'indikator_id');
    }
}
