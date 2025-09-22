<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Controls how database connections are managed.
    | You can define multiple database connections here (master, slave, etc.).
    |
    | By default, the system automatically uses PDO as the query driver when parameter binding is required.
    | For simple queries without parameters, MySQLi is used instead.
    |
    */
    'connections' => [
        'master' => [
            'host' => config('DB_HOST', '127.0.0.1'),  // Hostname or IP address
            'port' => config('DB_PORT', '3306'),  // Port number
            'database' => config('DB_DATABASE', 'your_database_name'),  // Database name
            'username' => config('DB_USERNAME', 'root'),  // Database username
            'password' => config('DB_PASSWORD', ''),  // Database password
            'unix_socket' => config('DB_SOCKET', ''),  // Unix socket (optional)
            'charset' => 'utf8mb4',  // Database charset
            'collation' => 'utf8mb4_unicode_ci',  // Collation type
            'prefix' => '',  // Table prefix (optional)
            'strict' => true,  // Enable strict mode for SQL queries
            'engine' => null,  // Database engine (e.g., InnoDB, MyISAM)
        ]
    ]
];
