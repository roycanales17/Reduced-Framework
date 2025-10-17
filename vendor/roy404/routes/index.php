<?php

	spl_autoload_register(fn($class) => array_map(fn($namespace, $baseDir) => str_starts_with($class, $namespace) && file_exists($file = $baseDir . str_replace('\\', '/', str_replace($namespace, '', $class)) . '.php') && require_once $file, array_keys($namespaces = ['App\\Routes\\' => __DIR__ . '/src/']), $namespaces));

	App\Routes\Route::configure(__DIR__, [
		'routes/web.php'
	])->captured(function($content) {
		echo $content;
	});