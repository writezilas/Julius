<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ChatSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    // Cache settings for performance
    protected static $cachedSettings = null;

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        if (self::$cachedSettings === null) {
            self::loadSettings();
        }

        if (!isset(self::$cachedSettings[$key])) {
            return $default;
        }

        $setting = self::$cachedSettings[$key];
        
        // Type casting based on setting type
        switch ($setting['type']) {
            case 'boolean':
                return filter_var($setting['value'], FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $setting['value'];
            case 'json':
                return json_decode($setting['value'], true);
            default:
                return $setting['value'];
        }
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'string', $description = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'description' => $description
            ]
        );

        // Clear cache
        self::$cachedSettings = null;
        Cache::forget('chat_settings');

        return $setting;
    }

    /**
     * Load all settings into cache
     */
    protected static function loadSettings()
    {
        self::$cachedSettings = Cache::remember('chat_settings', 60, function () {
            return self::all()->keyBy('key')->toArray();
        });
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        self::$cachedSettings = null;
        Cache::forget('chat_settings');
    }

    /**
     * Check if chat system is enabled
     */
    public static function isChatEnabled()
    {
        return self::get('chat_enabled', true);
    }

    /**
     * Get chat character limit
     */
    public static function getCharacterLimit()
    {
        return self::get('chat_character_limit', 100);
    }

    /**
     * Check if file uploads are enabled
     */
    public static function isFileUploadEnabled()
    {
        return self::get('chat_file_upload_enabled', true);
    }

    /**
     * Get maximum file size in KB
     */
    public static function getMaxFileSize()
    {
        return self::get('chat_max_file_size', 5120);
    }

    /**
     * Get all settings for admin interface
     */
    public static function getAllSettings()
    {
        return self::orderBy('key')->get();
    }

    /**
     * Get settings with their current values and metadata
     */
    public static function getSettingsForAdmin()
    {
        $settings = self::getAllSettings();
        
        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting->key] = [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'description' => $setting->description,
                'current_value' => self::get($setting->key)
            ];
        }
        
        return $formatted;
    }

    /**
     * Model events
     */
    protected static function booted()
    {
        // Clear cache when settings are updated
        static::saved(function () {
            self::clearCache();
        });
        
        static::deleted(function () {
            self::clearCache();
        });
    }
}
