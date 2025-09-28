<?php
    use App\Console\Schedule;

    Schedule::command('clear:logs', [true])->daily();
