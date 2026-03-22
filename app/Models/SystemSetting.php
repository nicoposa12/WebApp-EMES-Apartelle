<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    /**
     * Get setting value with automatic type casting
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;

        switch ($setting->type) {
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $setting->value;
            case 'json':
                return json_decode($setting->value, true);
            default:
                return $setting->value;
        }
    }

    /**
     * Set setting value with automatic type detection
     */
    public static function set($key, $value)
    {
        $type = 'string';
        if (is_bool($value)) $type = 'boolean';
        elseif (is_int($value) || is_float($value)) $type = 'integer';
        elseif (is_array($value) || is_object($value)) {
            $type = 'json';
            $value = json_encode($value);
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type]
        );
    }
}
