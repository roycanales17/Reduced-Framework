<?php

    // Suppress errors from being displayed to the end user during configuration.
    error_reporting(0);
    ini_set('display_errors', '0');

    // PSR-4 style class autoloader
    spl_autoload_register(function (string $class): void {
        $path = '../' . lcfirst(str_replace('\\', '/', $class)) . '.php';

        if (file_exists($path)) {
            require_once $path;
        }
    });

    // Load Composer dependencies and core bootstrap
    require_once '../vendor/autoload.php';
    require_once '../app/Bootstrap.php';
