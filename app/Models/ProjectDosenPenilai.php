<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDosenPenilai extends Model
{
    use HasFactory;

    protected $table = 'project_dosen_penilai';

    protected $fillable = [
        'project_card_id',
        'dosen_id',
        'mata_kuliah_id',
        'status_penilaian',
        'assigned_by',
    ];

    public function projectCard(): BelongsTo
    {
        return $this->belongsTo(ProjectCard::class, 'project_card_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
