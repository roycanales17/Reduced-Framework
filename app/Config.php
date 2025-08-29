<?php

use App\Utilities\Blueprints\CacheDriver;

return [

/*
|--------------------------------------------------------------------------
| Preloaded Files
|--------------------------------------------------------------------------
|
| Specify custom PHP files to include before the application handles any routes.
| These can contain helper functions, macros, or any bootstrap logic.
|
*/
'preload_files' => [],

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
	'driver' => 'file',      	 // Supported: file, database, redis
	'lifetime' => 120,           // Session lifetime in minutes
	'expire_on_close' => false,  // Whether session expires when the browser closes
	'encrypt' => false,          // Encrypt session data (if you implement encryption)
	'path' => '/',               // Path where the session is available
	'domain' => null,            // Cookie domain
	'secure' => false,           // Only send cookie over HTTPS
	'http_only' => true,         // Prevent JavaScript access to the cookie
	'same_site' => 'Lax',        // Options: Lax, Strict, None
	'storage_path' => '../storage/private/sessions', // For 'file' driver
],

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
|
| Controls how database connections are managed.
| You can define multiple database connections here (MySQL, SQLite, PostgreSQL, etc.).
|
*/
'database' => [
	'default' => 'master',  // The default database connection to use

	'connections' => [
		'master' => [
			'driver' => 'mysql',  // Database type
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
		],

		// For now only (mysql/pdo) is supported...
	]
],


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
'cache' => [
	// The default cache driver to use: 'redis' or 'memcached'
	'driver' => 'memcached',

	// Redis configuration
	'redis' => [
		'driver' => CacheDriver::Redis,
		'server' => config('REDIS_SERVER_NAME', 'redis'),
		'port' => config('REDIS_PORT', '6379')
	],

	// Memcached configuration
	'memcached' => [
		'driver' => CacheDriver::Memcached,
		'server' => config('MEMCACHE_SERVER_NAME', 'memcached'),
		'port' => config('MEMCACHE_PORT', '11211')
	]
],

/*
|--------------------------------------------------------------------------
| Global Variables
|--------------------------------------------------------------------------
|
| Defines global constants via the `define()` function.
| This allows for the creation of global variables across the application.
|
*/
'defines' => require 'Variables.php',

/*
|--------------------------------------------------------------------------
| Route Configuration
|--------------------------------------------------------------------------
|
| Define route-specific settings and content capturing behaviors for both
| web and API routes. These handlers can be used for templating or raw output.
|
*/
'routes' => require 'Routes.php',

/*
|--------------------------------------------------------------------------
| Mailing Configuration
|--------------------------------------------------------------------------
|
| This section controls the application's outbound mailing capabilities.
| You can configure SMTP credentials and toggle mailing on or off via
| environment variables. This setup is compatible with providers such
| as Mailgun, SendGrid, and custom SMTP servers.
|
*/
'mailing' => require 'Mailing.php'

];
