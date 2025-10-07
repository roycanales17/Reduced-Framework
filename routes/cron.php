<?php

    use App\Console\Schedule;

    /*
    |--------------------------------------------------------------------------
    | Console Schedule
    |--------------------------------------------------------------------------
    |
    | Here is where you can define all of your application's scheduled tasks.
    | These commands will be executed automatically according to the defined
    | frequency. Keep your application clean and efficient!
    |
    */

    Schedule::command('clear:logs', [true])->daily();
