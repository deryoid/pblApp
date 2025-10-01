<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectCard extends Model
{
    use HasFactory;

    protected $table = 'project_cards';

    protected $guarded = ['id'];

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

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
