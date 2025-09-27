<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationRubricGroup extends Model
{
    protected $table = 'evaluation_rubric_groups';
    protected $fillable = ['code','name','weight','parent_code'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_code', 'code');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_code', 'code');
    }

    public function indicators()
    {
        return $this->hasMany(EvaluationRubricIndicator::class, 'group_code', 'code');
    }
}
