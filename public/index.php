<?php

    use App\Bootstrap\Application;

    // PSR-4 style class autoloader
    spl_autoload_register(function (string $class): void {
        $path = '../' . lcfirst(str_replace('\\', '/', $class)) . '.php';

        if (file_exists($path)) {
            require_once $path;
        }
    });

    // Load Composer dependencies and core bootstrap
    require_once '../vendor/autoload.php';
    Application::boot(base_path('.env'))->run();
