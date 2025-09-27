<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationSetting extends Model
{
    protected $table = 'evaluation_settings';
    protected $fillable = ['key','value'];
    public $timestamps = true;

    public static function get($key, $default = null)
    {
        $row = static::where('key',$key)->first();
        return $row ? (is_numeric($row->value) ? (0 + $row->value) : $row->value) : $default;
    }

    public static function set($key, $value)
    {
        return static::updateOrCreate(['key'=>$key], ['value'=>(string)$value]);
    }
}
