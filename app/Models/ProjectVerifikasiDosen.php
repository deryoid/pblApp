<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectVerifikasiDosen extends Model
{
    use HasFactory;

    protected $table = 'project_verifikasi_dosen';

    protected $fillable = [
        'project_card_id',
        'dosen_id',
        'peran',
        'status',
        'catatan',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function projectCard(): BelongsTo
    {
        return $this->belongsTo(ProjectCard::class, 'project_card_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function scopeAcc($query)
    {
        return $query->where('status', 'acc');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isAcc(): bool
    {
        return $this->status === 'acc';
    }

    public function isRevisi(): bool
    {
        return $this->status === 'revisi';
    }
}
