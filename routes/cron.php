<?php
    use App\Console\Schedule;

    Schedule::command('clear:logs')->daily();
    Schedule::command('clear:cache')->daily();
