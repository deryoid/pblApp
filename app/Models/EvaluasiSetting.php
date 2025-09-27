<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluasiSetting extends Model
{
    // Samakan dengan migration: gunakan tabel 'evaluation_settings'
    protected $table = 'evaluation_settings';
    protected $guarded = ['id'];

    /**
     * Ambil banyak setting sekaligus.
     */
    public static function getMany(array $keys, array $defaults = [])
    {
        $rows = self::whereIn('key', $keys)->pluck('value','key')->toArray();
        $data = [];
        foreach ($keys as $k) {
            $val = $rows[$k] ?? ($defaults[$k] ?? null);
            // auto cast ke int kalau numeric
            $data[$k] = is_numeric($val) ? (int) $val : $val;
        }
        return $data;
    }

    /**
     * Simpan banyak setting sekaligus.
     */
    public static function putMany(array $data)
    {
        foreach ($data as $k => $v) {
            self::updateOrCreate(
                ['key' => $k],
                ['value' => (string)$v]
            );
        }
    }

    public static function get(string $key, $default = null)
    {
        $row = self::where('key', $key)->first();
        if (!$row) return $default;
        return is_numeric($row->value) ? (int) $row->value : $row->value;
    }
}
