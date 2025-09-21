<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AktivitasCard extends Model
{
    use HasFactory;

    protected $table = 'aktivitas_cards';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_aktivitas' => 'date',
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

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function listAktivitas()
    {
        return $this->belongsTo(AktivitasList::class, 'list_aktivitas_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
