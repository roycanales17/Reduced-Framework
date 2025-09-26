<?php

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
'session' => require 'Session.php',

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
|
| Controls how database connections are managed.
| You can define multiple database connections here (MySQL, SQLite, PostgreSQL, etc.).
|
*/
'database' => require 'Database.php',


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
'cache' => require 'Cache.php',

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
'mailing' => require 'Mailing.php',

/*
|--------------------------------------------------------------------------
| Cron Job - Scheduler
|--------------------------------------------------------------------------
|
| This section defines how the application handles scheduled tasks.
|
| - 'scheduler' : The entrypoint for the internal scheduler system.
|                 This file is invoked by the system cron every minute.
|                 It checks all registered tasks and runs due ones.
|
| - 'cron'      : Optional file where you can define or register
|                 custom cron-based tasks (similar to routes).
|                 This allows you to centralize all your scheduled
|                 jobs in one place.
|
| - 'artisan'   : This performs the command executed by the scheduler.
|
|
| 🔧 System Setup:
| ----------------
| To enable scheduled tasks, add the following to your system crontab:
|
|   * * * * * /usr/bin/php /path/to/project/scheduler >> /dev/null 2>&1
|
| This executes the scheduler every minute. The scheduler will then
| trigger any Artisan commands or closures that are due.
| You only need ONE cron entry — the application handles the rest.
*/
'execute' => '/artisan',
'cron' => '/routes/cron.php',

];
