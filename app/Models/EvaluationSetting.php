<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationSetting extends Model
{
    protected $table = 'evaluation_settings';

    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    /**
     * Ambil nilai setting berdasarkan key.
     */
    public static function get($key, $default = null)
    {
        $row = static::where('key', $key)->first();

        return $row ? (is_numeric($row->value) ? (0 + $row->value) : $row->value) : $default;
    }

    /**
     * Simpan setting berdasarkan key.
     */
    public static function set($key, $value)
    {
        return static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    /**
     * Ambil banyak setting sekaligus.
     */
    public static function getMany(array $keys, array $defaults = []): array
    {
        $rows = self::whereIn('key', $keys)->pluck('value', 'key')->toArray();
        $data = [];
        foreach ($keys as $k) {
            $val = $rows[$k] ?? ($defaults[$k] ?? null);
            $data[$k] = is_numeric($val) ? (int) $val : $val;
        }

        return $data;
    }

    /**
     * Simpan banyak setting sekaligus.
     */
    public static function putMany(array $data): void
    {
        foreach ($data as $k => $v) {
            self::updateOrCreate(
                ['key' => $k],
                ['value' => (string) $v]
            );
        }
    }
}
