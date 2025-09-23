<?php
// app/Models/EvaluasiAbsensi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiAbsensi extends Model
{
    protected $table = 'evaluasi_absensi';
    protected $guarded = ['id'];

    protected $casts = [
        'waktu_absen' => 'datetime',
    ];

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(EvaluasiSesi::class, 'sesi_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
