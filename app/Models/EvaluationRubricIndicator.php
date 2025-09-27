<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationRubricIndicator extends Model
{
    protected $table = 'evaluation_rubric_indicators';
    protected $fillable = ['group_code','code','name','weight'];

    public function group()
    {
        return $this->belongsTo(EvaluationRubricGroup::class, 'group_code', 'code');
    }
}
