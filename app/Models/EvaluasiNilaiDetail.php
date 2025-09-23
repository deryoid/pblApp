<?php
// app/Models/EvaluasiNilaiDetail.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiNilaiDetail extends Model
{
    protected $table = 'evaluasi_nilai_detail';
    protected $guarded = ['id'];

    protected $casts = [
        'skor' => 'integer',
    ];

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(EvaluasiSesi::class, 'sesi_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(EvaluasiIndikator::class, 'indikator_id');
    }
}
