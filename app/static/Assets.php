<?php
    /**
     * CORS + Static Asset Configuration
     */
    return [

        // 🔹 Paths to apply (for your build and resources)
        'paths' => ['build/*', 'resources/*'],

        // 🔹 CORS settings
        'allowed_methods' => ['GET', 'OPTIONS'],
        'allowed_origins' => ['*'],
        'allowed_headers' => ['X-Requested-With', 'Content-Type', 'Origin', 'Authorization'],

        // 🔹 Static file extensions handled
        'file_extensions' => ['js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'woff', 'woff2'],

        // 🔹 Cache settings (seconds)
        'cache_max_age' => 31536000, // 1 year for Cache-Control

        // 🔹 Expiration rules for mod_expires.c (Apache)
        'expires' => [
            'default' => 'access plus 1 month',

            // By MIME type
            'image/jpg'  => 'access plus 1 year',
            'image/jpeg' => 'access plus 1 year',
            'image/png'  => 'access plus 1 year',
            'image/gif'  => 'access plus 1 year',
            'image/webp' => 'access plus 1 year',
            'image/svg+xml' => 'access plus 1 year',

            'text/css' => 'access plus 1 month',
            'application/javascript' => 'access plus 1 month',
            'application/x-javascript' => 'access plus 1 month',

            'font/woff' => 'access plus 1 year',
            'font/woff2' => 'access plus 1 year',
            'application/font-woff2' => 'access plus 1 year',
        ],
    ];
