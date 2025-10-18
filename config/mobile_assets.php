<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mobile Asset Optimization Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for optimizing assets
    | specifically for mobile devices to improve loading times and reduce
    | timeout errors on slower mobile connections.
    |
    */

    'mobile_asset_optimization' => env('MOBILE_ASSET_OPTIMIZATION', true),

    /*
    |--------------------------------------------------------------------------
    | Asset Loading Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how assets are loaded and optimized for mobile devices
    |
    */

    'lazy_load_images' => env('MOBILE_LAZY_LOAD_IMAGES', true),
    'compress_css_js' => env('MOBILE_COMPRESS_ASSETS', true),
    'mobile_asset_timeout' => env('MOBILE_ASSET_TIMEOUT', 30), // seconds
    
    /*
    |--------------------------------------------------------------------------
    | Critical Assets
    |--------------------------------------------------------------------------
    |
    | These assets should be preloaded for mobile devices to ensure
    | essential functionality is available even on slow connections
    |
    */

    'preload_critical_assets' => [
        'css' => [
            'css/bootstrap.min.css',
            'css/icons.min.css',
            'css/app.min.css',
            'css/mobile-payment-modal-fix.css',
        ],
        'js' => [
            'js/jquery-3.6.0.min.js',
            'js/bootstrap.bundle.min.js',
            'js/mobile-network-handler.js',
            'js/mobile-payment-modal-fix.js',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile-Specific Assets
    |--------------------------------------------------------------------------
    |
    | Assets that should only be loaded on mobile devices
    |
    */

    'mobile_only_assets' => [
        'css' => [
            'css/ios-payment-modal-fix.css',
            'css/mobile-optimizations.css',
        ],
        'js' => [
            'js/ios-payment-modal-fix.js',
            'js/mobile-touch-handlers.js',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Asset Minification and Compression
    |--------------------------------------------------------------------------
    |
    | Configure asset minification and compression for mobile optimization
    |
    */

    'minification' => [
        'enabled' => env('MOBILE_MINIFY_ASSETS', true),
        'css_minify' => true,
        'js_minify' => true,
        'html_minify' => false, // Can cause issues with blade templates
    ],

    'compression' => [
        'gzip_enabled' => env('MOBILE_GZIP_ENABLED', true),
        'compression_level' => env('MOBILE_COMPRESSION_LEVEL', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure asset caching specifically for mobile devices
    |
    */

    'cache_mobile_assets' => env('MOBILE_CACHE_ASSETS', true),
    
    'cache_settings' => [
        'css_cache_time' => env('MOBILE_CSS_CACHE_TIME', 3600 * 24 * 7), // 1 week
        'js_cache_time' => env('MOBILE_JS_CACHE_TIME', 3600 * 24 * 7),   // 1 week
        'image_cache_time' => env('MOBILE_IMAGE_CACHE_TIME', 3600 * 24 * 30), // 1 month
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | Configure CDN settings for mobile asset delivery
    |
    */

    'cdn' => [
        'enabled' => env('MOBILE_CDN_ENABLED', false),
        'url' => env('MOBILE_CDN_URL', ''),
        'fallback_enabled' => true,
        'timeout' => env('MOBILE_CDN_TIMEOUT', 10), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Progressive Loading
    |--------------------------------------------------------------------------
    |
    | Configure progressive loading for mobile devices to prioritize
    | critical assets and defer non-essential resources
    |
    */

    'progressive_loading' => [
        'enabled' => env('MOBILE_PROGRESSIVE_LOADING', true),
        
        // Assets to load immediately
        'priority_assets' => [
            'css/bootstrap.min.css',
            'js/jquery-3.6.0.min.js',
            'js/mobile-network-handler.js',
        ],
        
        // Assets to defer until after page load
        'deferred_assets' => [
            'js/charts.min.js',
            'js/analytics.min.js',
            'css/animations.css',
        ],
        
        // Assets to load only when needed
        'lazy_assets' => [
            'js/payment-processing.js',
            'js/dashboard-charts.js',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    |
    | Configure image optimization for mobile devices
    |
    */

    'image_optimization' => [
        'enabled' => env('MOBILE_IMAGE_OPTIMIZATION', true),
        'lazy_load' => true,
        'responsive_images' => true,
        'webp_support' => env('MOBILE_WEBP_SUPPORT', true),
        'quality' => [
            'mobile' => 80,
            'tablet' => 85,
            'desktop' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection-Based Optimization
    |--------------------------------------------------------------------------
    |
    | Adjust asset loading based on connection quality
    |
    */

    'connection_optimization' => [
        'enabled' => env('MOBILE_CONNECTION_OPTIMIZATION', true),
        
        'slow_connection' => [
            'threshold' => 5000, // milliseconds
            'reduce_quality' => true,
            'defer_non_critical' => true,
            'enable_placeholders' => true,
        ],
        
        'fast_connection' => [
            'threshold' => 1000, // milliseconds
            'preload_next_page' => true,
            'full_quality_images' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | Configure how asset loading errors are handled on mobile devices
    |
    */

    'error_handling' => [
        'retry_attempts' => 3,
        'retry_delay' => 2000, // milliseconds
        'fallback_assets' => [
            'css/bootstrap.min.css' => 'css/bootstrap-fallback.css',
            'js/jquery-3.6.0.min.js' => 'js/jquery-fallback.min.js',
        ],
        'show_loading_indicators' => env('MOBILE_SHOW_LOADING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Monitor asset loading performance on mobile devices
    |
    */

    'performance_monitoring' => [
        'enabled' => env('MOBILE_PERFORMANCE_MONITORING', true),
        'log_slow_assets' => true,
        'slow_asset_threshold' => 5000, // milliseconds
        'track_failed_loads' => true,
    ],

];