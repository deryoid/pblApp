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
            $peran = $existing ? ($existing['peran'].' & Pembimbing Proyek') : 'Dosen Penentu / Pembimbing Proyek';
            $involvedMap->put($this->dosen_penentu_id, [
                'dosen' => $this->dosenPenentu,
                'peran' => $peran,
                'peran_tipe' => 'penentu',
                'mata_kuliah' => null,
            ]);
        }

        // Gabungkan dengan data verifikasi / ACC dari tabel project_verifikasi_dosen
        $verifGrouped = $this->verifikasiDosen->keyBy('dosen_id');

        return $involvedMap->map(function ($item, $dosenId) use ($verifGrouped) {
            $verif = $verifGrouped->get($dosenId);
            $status = $verif ? $verif->status : 'pending';

            return (object) [
                'dosen_id' => $dosenId,
                'dosen' => $item['dosen'],
                'peran' => $verif && ! empty($verif->peran) ? $verif->peran : $item['peran'],
                'peran_tipe' => $item['peran_tipe'] ?? 'pengampu',
                'mata_kuliah' => $item['mata_kuliah'] ?? null,
                'status' => $status,
                'is_acc' => $status === 'acc',
                'is_revisi' => $status === 'revisi',
                'is_pending' => $status === 'pending',
                'catatan' => $verif?->catatan,
                'verified_at' => $verif?->verified_at,
                'penilai_record' => $item['penilai_record'] ?? null,
            ];
        })->values();
    }

    /**
     * Pastikan semua dosen yang terlibat memiliki record verifikasi di tabel project_verifikasi_dosen.
     */
    public function syncVerifikasiDosen(): \Illuminate\Support\Collection
    {
        $involved = $this->getInvolvedDosen();

        foreach ($involved as $item) {
            \App\Models\ProjectVerifikasiDosen::firstOrCreate(
                [
                    'project_card_id' => $this->id,
                    'dosen_id' => $item->dosen_id,
                ],
                [
                    'peran' => $item->peran,
                    'status' => 'pending',
                ]
            );
        }

        $this->load('verifikasiDosen');

        return $this->getInvolvedDosen();
    }

    /**
     * Hitung ringkasan status ACC dan perbarui status_proposal project_cards.
     */
    public function updateStatusProposalAcc(): string
    {
        $involved = $this->getInvolvedDosen();

        if ($involved->isEmpty()) {
            return $this->status_proposal ?? 'pending';
        }

        $hasRevisi = $involved->contains(fn ($i) => $i->status === 'revisi');
        $allAcc = $involved->every(fn ($i) => $i->status === 'acc');

        $newStatus = 'pending';
        if ($hasRevisi) {
            $newStatus = 'revisi';
        } elseif ($allAcc) {
            $newStatus = 'disetujui';
        }

        if ($this->status_proposal !== $newStatus) {
            $this->status_proposal = $newStatus;
            $this->save();
        }

        return $newStatus;
    }
}
