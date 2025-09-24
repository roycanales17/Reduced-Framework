<?php

use App\Bootstrap\Application;

Application::boot()
->withEnvironment('../.env')
->withConfiguration('../app/Config.php')
->run(function ($config) {
    // Initialize your application here before routes are executed.
});
