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

    protected function casts(): array
    {
        return [
            'kelompok_id' => 'integer',
            'periode_id' => 'integer',
            'list_aktivitas_id' => 'integer',
            'position' => 'integer',
            'tanggal_aktivitas' => 'date',
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

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return $this->where($field, $value)->first() ?? abort(404);
        }

        if (is_numeric($value) && ctype_digit((string) $value)) {
            return $this->where('id', (int) $value)->first()
                ?? $this->where('uuid', (string) $value)->first()
                ?? abort(404);
        }

        return $this->where('uuid', (string) $value)->first() ?? abort(404);
    }

    public function listAktivitas()
    {
        return $this->belongsTo(AktivitasList::class, 'list_aktivitas_id');
    }

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
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
