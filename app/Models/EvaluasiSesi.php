<?php
// app/Models/EvaluasiSesi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EvaluasiSesi extends Model
{
    protected $table = 'evaluasi_sesi';
    protected $guarded = ['id'];

    public const ST_BELUM   = 'Belum dijadwalkan';
    public const ST_JADWAL  = 'Terjadwal';
    public const ST_PROSES  = 'Berlangsung';
    public const ST_SELESAI = 'Selesai';
    public const ST_BATAL   = 'Dibatalkan';

    protected $casts = [
        'jadwal_mulai'   => 'datetime',
        'jadwal_selesai' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->uuid)) $m->uuid = (string) Str::uuid();
            if (empty($m->status)) $m->status = self::ST_BELUM;
        });
    }

    // relasi
    public function periode(){ return $this->belongsTo(Periode::class); }
    public function kelompok(){ return $this->belongsTo(Kelompok::class); }
    public function evaluator(){ return $this->belongsTo(User::class,'evaluator_id'); }
    public function absensis(){ return $this->hasMany(EvaluasiAbsensi::class,'sesi_id'); }
    public function sesiIndikators(){ return $this->hasMany(EvaluasiSesiIndikator::class,'sesi_id')->orderBy('urutan'); }
    public function nilaiDetails(){ return $this->hasMany(EvaluasiNilaiDetail::class,'sesi_id'); }

    /** Pastikan setiap kelompok di periode ini punya satu sesi (idempotent). Return: jumlah sesi yang dibuat. */
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
                'status'      => self::ST_BELUM,
            ]);
        }
        return $missing->count();
    }

    /** Pastikan satu kelompok punya sesi untuk periode tertentu. Return model sesi. */
    public static function ensureForKelompok(int $periodeId, int $kelompokId, ?int $creatorId = null): self
    {
        return static::firstOrCreate(
            ['periode_id'=>$periodeId, 'kelompok_id'=>$kelompokId],
            ['created_by'=>$creatorId, 'status'=>self::ST_BELUM]
        );
    }

    /** Urutan status yang enak untuk ditampilkan */
    public function scopeOrderByStatus($q)
    {
        $order = [self::ST_BELUM, self::ST_JADWAL, self::ST_PROSES, self::ST_SELESAI, self::ST_BATAL];
        $case  = "CASE status ".
                 implode(' ', array_map(fn($s,$i)=>"WHEN '$s' THEN $i", $order, array_keys($order))).
                 " ELSE 999 END";
        return $q->orderByRaw($case);
    }
}
