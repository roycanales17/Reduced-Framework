<?php
    use App\Console\Schedule;

    Schedule::command('clear:logs', [
        'force' => true
    ])->everyMinute();
