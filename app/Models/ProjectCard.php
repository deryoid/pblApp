<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectCard extends Model
{
    use HasFactory;

    protected $table = 'project_cards';

    public const SKEMA_CATEGORIES = [
        'Internal',
        'External',
        'Hibah/Penelitian',
        'Lomba',
        'Teaching Factory',
    ];

    protected $guarded = ['id'];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return $this->where($field, $value)->first() ?? abort(404);
        }

        // Jika string berupa angka murni, cari berdasarkan primary key id (atau uuid jika cocok)
        if (is_numeric($value) && ctype_digit((string) $value)) {
            return $this->where('id', (int) $value)->first()
                ?? $this->where('uuid', (string) $value)->first()
                ?? abort(404);
        }

        // Jika bukan angka murni (UUID string), cari HANYA pada kolom uuid untuk mencegah MySQL type juggling ke kolom id
        return $this->where('uuid', (string) $value)->first() ?? abort(404);
    }

    public function getProposalUrlAttribute(): ?string
    {
        return $this->proposal_file_path ? asset('storage/'.$this->proposal_file_path) : null;
    }

    public function hasProposal(): bool
    {
        return ! empty($this->proposal_file_path);
    }

    protected function casts(): array
    {
        return [
            'kelompok_id' => 'integer',
            'periode_id' => 'integer',
            'list_id' => 'integer',
            'position' => 'integer',
            'labels' => 'array',
            'due_date' => 'datetime',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'biaya_barang' => 'decimal:2',
            'biaya_jasa' => 'decimal:2',
            'progress' => 'integer',
            'comments_count' => 'integer',
            'attachments_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $card) {
            if (empty($card->uuid)) {
                $card->uuid = (string) Str::uuid();
            }
            if ($card->position === null) {
                $card->position = 0;
            }
        });
    }

    public function kelompok()
    {
        return $this->belongsTo(\App\Models\Kelompok::class);
    }

    public function periode()
    {
        return $this->belongsTo(\App\Models\Periode::class);
    }

    public function list()
    {
        return $this->belongsTo(\App\Models\ProjectList::class, 'list_id');
    }

    public function projectList()
    {
        return $this->belongsTo(\App\Models\ProjectList::class, 'list_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function evaluasiMitra()
    {
        return $this->hasMany(\App\Models\EvaluasiMitra::class, 'project_card_id');
    }

    public function dosenPenentu(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'dosen_penentu_id');
    }

    public function mataKuliahs(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\MataKuliah::class, 'project_mata_kuliah')
            ->withTimestamps();
    }

    public function dosenPenilai(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ProjectDosenPenilai::class, 'project_card_id');
    }

    public function penilaianDosen(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PenilaianProyekDosen::class, 'project_card_id');
    }

    public function verifikasiDosen(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ProjectVerifikasiDosen::class, 'project_card_id');
    }

    /**
     * Dapatkan daftar seluruh dosen yang terlibat pada proyek ini beserta status ACC / verifikasinya.
     */
    public function getInvolvedDosen(): \Illuminate\Support\Collection
    {
        $this->loadMissing([
            'mataKuliahs.dosenPengampu',
            'dosenPenilai.dosen',
            'dosenPenilai.mataKuliah',
            'dosenPenentu',
        ]);

        $this->load('verifikasiDosen.dosen');

        $involvedMap = collect();

        // 1. Dosen Pengampu dari Mata Kuliah Terkait
        foreach ($this->mataKuliahs as $mk) {
            foreach ($mk->dosenPengampu as $dp) {
                if (! $involvedMap->has($dp->id)) {
                    $involvedMap->put($dp->id, [
                        'dosen' => $dp,
                        'peran' => 'Dosen Pengampu MK: '.$mk->nama_matkul,
                        'peran_tipe' => 'pengampu',
                        'mata_kuliah' => $mk->nama_matkul,
                    ]);
                }
            }
        }

        // 2. Dosen Penilai Khusus
        foreach ($this->dosenPenilai as $dPenilai) {
            if ($dPenilai->dosen) {
                $mkNama = $dPenilai->mataKuliah?->nama_matkul ?: 'Semua MK';
                $existing = $involvedMap->get($dPenilai->dosen_id);
                $peran = $existing ? ($existing['peran'].' & Penilai Proyek') : 'Dosen Penilai Proyek ('.$mkNama.')';
                $involvedMap->put($dPenilai->dosen_id, [
                    'dosen' => $dPenilai->dosen,
                    'peran' => $peran,
                    'peran_tipe' => 'penilai',
                    'mata_kuliah' => $mkNama,
                    'penilai_record' => $dPenilai,
                ]);
            }
        }

        // 3. Dosen Penentu Proyek
        if ($this->dosenPenentu) {
            $existing = $involvedMap->get($this->dosen_penentu_id);
            $peran = $existing ? ($existing['peran'].' & Pembimbing Proyek') : 'Dosen Pembimbing Proyek';
            $involvedMap->put($this->dosen_penentu_id, [
                'dosen' => $this->dosenPenentu,
                'peran' => $peran,
                'peran_tipe' => 'penentu',
                'mata_kuliah' => null,
            ]);
        }

        return $involvedMap->map(function ($item, $dosenId) {
            return (object) [
                'dosen_id' => $dosenId,
                'dosen' => $item['dosen'],
                'nama_dosen' => $item['dosen']?->nama_user ?? 'Dosen',
                'nip' => $item['dosen']?->username ?? '-',
                'peran' => $item['peran'],
                'peran_tipe' => $item['peran_tipe'] ?? 'pengampu',
                'mata_kuliah' => $item['mata_kuliah'] ?? null,
                'penilai_record' => $item['penilai_record'] ?? null,
            ];
        })->values();
    }

    /**
     * Helper kompatibilitas lama (ACC proposal telah dihapus).
     */
    public function syncVerifikasiDosen(): \Illuminate\Support\Collection
    {
        return $this->getInvolvedDosen();
    }

    /**
     * Helper kompatibilitas lama (ACC proposal telah dihapus).
     */
    public function updateStatusProposalAcc(): ?string
    {
        return null;
    }

    /**
     * Scope untuk membatasi kartu proyek yang hanya dapat diakses/dilihat oleh dosen pengampu / penilai / pembimbing.
     */
    public function scopeAccessibleByDosen($query, ?User $user)
    {
        if (! $user || $user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereHas('mataKuliahs', function ($mq) use ($user) {
                $mq->where(function ($subMq) use ($user) {
                    $subMq->whereHas('dosenPengampu', function ($dpq) use ($user) {
                        $dpq->where('users.id', $user->id);
                    })->orWhere('mata_kuliah.dosen_id', $user->id);
                });
            })
                ->orWhereHas('dosenPenilai', function ($dq) use ($user) {
                    $dq->where('dosen_id', $user->id);
                });
        });
    }

    /**
     * Memeriksa apakah seorang pengguna dapat melihat proyek ini.
     * Admin dan Mahasiswa anggota kelompok dapat melihat proyek.
     * Dosen hanya dapat melihat proyek jika mengampu mata kuliah terkait atau ditugaskan sebagai penilai/pembimbing.
     */
    public function canBeViewedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin() || $user->isMahasiswa()) {
            return true;
        }

        return $this->canBeAssessedBy($user);
    }

    /**
     * Memeriksa apakah seorang pengguna (Admin atau Dosen) berwenang menilai proyek ini.
     * Admin berwenang menilai seluruh proyek.
     * Dosen atau akun yang ditugaskan (pembimbing / penilai / pengampu) berwenang menilai proyek ini.
     */
    public function canBeAssessedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        // 1. Admin selalu berwenang menilai seluruh proyek
        if ($user->isAdmin()) {
            return true;
        }

        // 2. Dosen Penentu / Pembimbing pada proyek ini
        if ($this->dosen_penentu_id == $user->id) {
            return true;
        }

        // 3. Ditugaskan khusus oleh Admin sebagai Dosen Penilai / Penguji pada proyek ini
        if ($this->dosenPenilai()->where('dosen_id', $user->id)->exists()) {
            return true;
        }

        if (! $user->isDosen()) {
            return false;
        }

        // 4. Dosen Pengampu dari salah satu mata kuliah yang terhubung dengan proyek ini
        return $this->mataKuliahs()->where(function ($mq) use ($user) {
            $mq->whereHas('dosenPengampu', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->orWhere('mata_kuliah.dosen_id', $user->id);
        })->exists();
    }
}
