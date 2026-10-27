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
    ];

    protected $guarded = ['id'];

    public function getProposalUrlAttribute(): ?string
    {
        return $this->proposal_file_path ? asset('storage/'.$this->proposal_file_path) : null;
    }

    public function hasProposal(): bool
    {
        return ! empty($this->proposal_file_path);
    }

    protected $casts = [
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

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
