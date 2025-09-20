<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectList extends Model
{
    use HasFactory;

    protected $table = 'project_lists';

    protected $guarded = ['id'];

    protected $casts = [
    ];

    protected static function booted(): void
    {
        static::creating(function (self $list) {
            if (empty($list->uuid)) {
                $list->uuid = (string) Str::uuid();
            }
            if ($list->position === null) {
                $list->position = 0;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function cards()
    {
        return $this->hasMany(ProjectCard::class, 'list_id');
    }
}
