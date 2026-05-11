<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $primaryKey = 'setting_id';
    public $incrementing = true;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_editable',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('setting_key', $key)->first();

        if (!$setting) {
            return $default;
        }

        $type = strtolower($setting->setting_type ?? 'string');

        return match ($type) {
            'boolean', 'bool' => filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN),
            'number', 'integer', 'int' => (int) $setting->setting_value,
            'float', 'double' => (float) $setting->setting_value,
            'json', 'array' => json_decode($setting->setting_value, true),
            default => $setting->setting_value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => is_array($value) ? json_encode($value) : $value,
                'setting_type' => $type,
                'is_editable' => true,
            ]
        );
    }
}
