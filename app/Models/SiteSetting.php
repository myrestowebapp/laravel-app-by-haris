<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;

        $value = $setting->value;
        if (!empty($setting->type)) {
            if ($setting->type === 'integer') return (int) $value;
            if ($setting->type === 'boolean') return (bool) filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } elseif (is_numeric($value)) {
            return (int) $value;
        }
        
        return $value;
    }

    public static function set($key, $value, $type = 'string')
    {
        $payload = ['value' => (string) $value];
        if (Schema::hasColumn('site_settings', 'type')) {
            $payload['type'] = $type;
        }

        return self::updateOrCreate(
            ['key' => $key],
            $payload
        );
    }
}
