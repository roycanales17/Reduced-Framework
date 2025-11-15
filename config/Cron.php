<?php
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
|
| 🔧 System Setup:
| ----------------
| To enable scheduled tasks, add the following to your system crontab:
|
|   * * * * * root /usr/local/bin/php /var/www/html/artisan cron:scheduler >> /var/log/cron/cron.log 2>&1
|
| This executes the scheduler every minute. The scheduler will then
| trigger any Artisan commands or closures that are due.
| You only need ONE cron entry — the application handles the rest.
*/
return [
    'path' => '/routes/cron.php'
];
