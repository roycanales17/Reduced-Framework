<?php
    if (!function_exists('env')) {
        function env(string $key, $default = null) {
            $value = getenv($key);
            return $value !== false ? $value : $default;
        }
    }

    /**
     * --------------------------------------------------------
     * 🚀 Static Assets + CORS Configuration
     * --------------------------------------------------------
     * This file defines all HTTP access and cache policies for
     * frontend build assets and public resources.
     *
     * Used by:
     *  - htaccess.sh → Generates public/.htaccess rules
     *  - PHP runtime → Provides fallback config when .env not set
     *
     * Includes:
     *  - CORS rules (origins, headers, methods)
     *  - File extension handling
     *  - Cache-Control & Expires rules
     */
    return [

        // 🔹 Paths to apply
        'paths' => explode(',', env('ASSET_PATHS', 'build/*,resources/*')),

        // 🔹 CORS settings
        'allowed_methods' => explode(',', env('ASSET_ALLOW_METHODS', 'GET,OPTIONS')),
        'allowed_origins' => explode(',', env('ASSET_ALLOW_ORIGINS', '*')),
        'allowed_headers' => explode(',', env('ASSET_ALLOW_HEADERS', 'X-Requested-With,Content-Type,Origin,Authorization')),

        // 🔹 Static file extensions handled
        'file_extensions' => explode(',', env('ASSET_FILE_EXTENSIONS', 'js,css,png,jpg,jpeg,gif,svg,webp,woff,woff2')),

        // 🔹 Cache settings (seconds)
        'cache_max_age' => (int) env('ASSET_CACHE_MAX_AGE', 31536000),

        // 🔹 Expiration rules for mod_expires.c (Apache)
        'expires' => [
            'default' => env('ASSET_EXPIRES_DEFAULT', 'access plus 1 month'),

            'image/jpg'  => env('ASSET_EXPIRES_IMAGE', 'access plus 1 year'),
            'image/jpeg' => env('ASSET_EXPIRES_IMAGE', 'access plus 1 year'),
            'image/png'  => env('ASSET_EXPIRES_IMAGE', 'access plus 1 year'),
            'image/gif'  => env('ASSET_EXPIRES_IMAGE', 'access plus 1 year'),
            'image/webp' => env('ASSET_EXPIRES_IMAGE', 'access plus 1 year'),
            'image/svg+xml' => env('ASSET_EXPIRES_IMAGE', 'access plus 1 year'),

            'text/css' => env('ASSET_EXPIRES_CSS', 'access plus 1 month'),
            'application/javascript' => env('ASSET_EXPIRES_JS', 'access plus 1 month'),
            'application/x-javascript' => env('ASSET_EXPIRES_JS', 'access plus 1 month'),

            'font/woff' => env('ASSET_EXPIRES_FONT', 'access plus 1 year'),
            'font/woff2' => env('ASSET_EXPIRES_FONT', 'access plus 1 year'),
            'application/font-woff2' => env('ASSET_EXPIRES_FONT', 'access plus 1 year'),
        ],
    ];
