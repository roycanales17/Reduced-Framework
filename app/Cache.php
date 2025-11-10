<?php

use App\Utilities\Blueprints\CacheDriver;

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Define the caching system used throughout the application.
    | Set the 'driver' key to either 'redis' or 'memcached' to
    | specify the default cache engine.
    |
    | Each cache driver supports its own server configuration:
    | - 'server': Hostname or IP of the cache server
    | - 'port': Port the cache service is listening on
    |
    | You can toggle or configure these settings based on your
    | infrastructure and caching preferences.
    |
    */
    'driver' => CacheDriver::Memcached->value, // The default cache driver to use: 'redis' or 'memcached'

    // Redis configuration
    'redis' => [
        'driver' => CacheDriver::Redis,
        'server' => env('REDIS_HOST', 'redis'),
        'port' => env('REDIS_PORT', '6379')
    ],

    // Memcached configuration
    'memcached' => [
        'driver' => CacheDriver::Memcached,
        'server' => env('MEMCACHE_HOST', 'memcached'),
        'port' => env('MEMCACHE_PORT', '11211')
    ]
];
