<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EvaluasiMaster extends Model
{
    protected $table = 'evaluasi_master';
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->uuid)) $m->uuid = (string) Str::uuid();
        });
    }

    // relasi
    public function periode(){ return $this->belongsTo(Periode::class); }
    public function kelompok(){ return $this->belongsTo(Kelompok::class); }
    public function absensis(){ return $this->hasMany(EvaluasiAbsensi::class,'sesi_id'); }
    public function sesiIndikators(){ return $this->hasMany(EvaluasiSesiIndikator::class,'sesi_id')->orderBy('urutan'); }
    public function nilaiDetails(){ return $this->hasMany(EvaluasiNilaiDetail::class,'sesi_id'); }

    /** Pastikan setiap kelompok di periode ini punya satu evaluasi master (idempotent). Return: jumlah evaluasi yang dibuat. */
    public static function ensureForPeriode(int $periodeId, $kelompokIds, ?int $creatorId = null): int
    {
        $kelompokIds = collect($kelompokIds)->unique()->filter()->values();
        if ($kelompokIds->isEmpty()) return 0;

        $existing = static::where('periode_id', $periodeId)
            ->whereIn('kelompok_id', $kelompokIds)->pluck('kelompok_id')->all();

        $missing = $kelompokIds->diff($existing);
        foreach ($missing as $kid) {
            static::create([
                'periode_id'  => $periodeId,
                'kelompok_id' => (int) $kid,
                'created_by'  => $creatorId,
            ]);
        }
        return $missing->count();
    }

    /** Pastikan satu kelompok punya evaluasi master untuk periode tertentu. Return model evaluasi master. */
    public static function ensureForKelompok(int $periodeId, int $kelompokId, ?int $creatorId = null): self
    {
        return static::firstOrCreate(
            ['periode_id'=>$periodeId, 'kelompok_id'=>$kelompokId],
            ['created_by'=>$creatorId]
        );
    }
}
