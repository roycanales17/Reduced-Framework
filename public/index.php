<?php
    $rootPath = dirname(__DIR__);
    $htaccessFile = $rootPath . '/public/.htaccess';
    $generatorScript = $rootPath . '/htaccess.sh';

    if (!file_exists($htaccessFile)) {
        if (file_exists($generatorScript)) {
            chmod($generatorScript, 0755);
            shell_exec("/bin/bash $generatorScript 2>&1");
        }

        if (!file_exists($htaccessFile)) {
            die("❌ Failed to generate public/.htaccess. Please run htaccess.sh manually.");
        }
    }

    spl_autoload_register(function (string $class): void {
        $path = '../' . lcfirst(str_replace('\\', '/', $class)) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    });

    require_once '../vendor/autoload.php';
    require_once '../app/Bootstrap.php';
