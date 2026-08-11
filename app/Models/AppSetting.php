<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            if (!Schema::hasTable('app_settings')) {
                return $default;
            }
            $setting = static::where('key', $key)->first();
            if (!$setting) return $default;
            
            $decoded = json_decode($setting->value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $setting->value;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        try {
            $formattedValue = is_array($value) || is_bool($value) ? json_encode($value) : (string) $value;
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $formattedValue]
            );
        } catch (\Throwable $e) {
            // Log fallback
        }
    }

    public static function isEvotingActive(): bool
    {
        return (bool) static::get('is_evoting_active', true);
    }

    public static function canStudentEditProfile(): bool
    {
        return (bool) static::get('allow_student_profile_edit', true);
    }
}
