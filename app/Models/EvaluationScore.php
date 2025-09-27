<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationScore extends Model
{
    protected $table = 'evaluation_scores';
    protected $fillable = ['sesi_id','mahasiswa_id','indicator_code','score','evaluated_by','notes'];

    public function indicator()
    {
        return $this->belongsTo(EvaluationRubricIndicator::class, 'indicator_code', 'code');
    }
}
