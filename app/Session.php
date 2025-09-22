<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Controls how sessions are managed in the application.
    | You can specify the handler, lifetime, storage path, and more.
    |
    */
    'session' => [
        'driver' => 'file',      	 // Supported: file, database (redis is not supported yet)
        'lifetime' => 120,           // Session lifetime in minutes
        'expire_on_close' => false,  // Whether session expires when the browser closes
        'encrypt' => false,          // Encrypt session data (if you implement encryption)
        'path' => '/',               // Path where the session is available
        'domain' => null,            // Cookie domain
        'secure' => false,           // Only send cookie over HTTPS
        'http_only' => true,         // Prevent JavaScript access to the cookie
        'same_site' => 'Lax',        // Options: Lax, Strict, None
        'storage_path' => '../storage/private/sessions', // For 'file' driver
    ]
];
