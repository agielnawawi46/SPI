<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Retrieve a setting value by key with optional default.
     * Automatically decodes JSON if possible.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting || $setting->value === null) {
            return $default;
        }

        $decoded = json_decode($setting->value, true);
        return (json_last_error() === JSON_ERROR_NONE && !is_numeric($setting->value)) ? $decoded : $setting->value;
    }

    /**
     * Set a setting value by key.
     * Automatically JSON-encodes arrays or objects without escaping slashes.
     */
    public static function set(string $key, mixed $value): static
    {
        $payload = is_array($value) || is_object($value)
            ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : (string) $value;

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $payload]
        );
    }
}
