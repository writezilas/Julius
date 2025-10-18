<?php

namespace App\Helpers;

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Cache;

class SettingHelper
{
    /**
     * Get a general setting value by key
     * 
     * @param string $key The setting key
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Cache the settings for 1 hour to improve performance
        $cacheKey = "general_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = GeneralSetting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }
    
    /**
     * Set a general setting value
     * 
     * @param string $key The setting key
     * @param mixed $value The setting value
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        try {
            GeneralSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
            
            // Clear the cache for this setting
            Cache::forget("general_setting_{$key}");
            
            return true;
        } catch (\Exception $e) {
            \Log::error('SettingHelper::set failed', [
                'key' => $key,
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get admin email setting
     * 
     * @param string $default Default email if not set
     * @return string
     */
    public static function getAdminEmail(string $default = 'admin@example.com'): string
    {
        return self::get('admin_email', $default);
    }
    
    /**
     * Get referral bonus setting
     * 
     * @param int $default Default bonus amount
     * @return int
     */
    public static function getReferralBonus(int $default = 0): int
    {
        return (int) self::get('reffaral_bonus', $default);
    }
    
    /**
     * Get bought time setting (in minutes)
     * 
     * @param int $default Default time in minutes
     * @return int
     */
    public static function getBoughtTime(int $default = 1440): int
    {
        return (int) self::get('bought_time', $default);
    }
    
    /**
     * Get application timezone setting
     * 
     * @param string $default Default timezone
     * @return string
     */
    public static function getAppTimezone(string $default = 'UTC'): string
    {
        return self::get('app_timezone', $default);
    }
    
    /**
     * Clear all settings cache
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        $settings = GeneralSetting::all();
        foreach ($settings as $setting) {
            Cache::forget("general_setting_{$setting->key}");
        }
    }
    
    /**
     * Get all settings as an associative array
     * 
     * @return array
     */
    public static function all(): array
    {
        return Cache::remember('all_general_settings', 3600, function () {
            $settings = GeneralSetting::all();
            $result = [];
            
            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->value;
            }
            
            return $result;
        });
    }
}