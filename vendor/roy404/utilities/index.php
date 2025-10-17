<?php
	require 'vendor/autoload.php';

	spl_autoload_register(function ($class) {
		$namespaces = [
			'App\\Bootstrap\\Exceptions\\' => __DIR__ . '/exceptions/',
			'App\\Bootstrap\\Handler\\'    => __DIR__ . '/core/handler/',
			'App\\Bootstrap\\Helper\\'     => __DIR__ . '/core/helper/',
			'App\\Utilities\\Handler\\'    => __DIR__ . '/utilities/handler/',
			'App\\Utilities\\Blueprints\\' => __DIR__ . '/utilities/blueprints/',
			'App\\Utilities\\'             => __DIR__ . '/utilities/',
			'App\\Bootstrap\\'             => __DIR__ . '/core/',
			'App\\Http\\'                  => __DIR__ . '/http/',
		];

		$path = str_replace('\\', '/', $class) . '.php';
		if (file_exists($path)) {
			require_once $path;
		} else {
			foreach ($namespaces as $namespace => $baseDir) {
				if (str_starts_with($class, $namespace)) {
					$relativeClass = substr($class, strlen($namespace));
					$file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

					if (file_exists($file)) {
						require_once $file;
						return;
					}
				}
			}
		}
	});

	use App\Bootstrap\Application;
	use App\Bootstrap\Handler\RuntimeException;
	use App\Bootstrap\Exceptions\AppException;

	$app = Application::boot();
	$app->withExceptions(function(RuntimeException $exception) {
		$exception->report(function(AppException $exception) {
			echo '<pre>';
			print_r([
				'message' => $exception->getMessage(),
				'code' => $exception->getCode(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
			]);
			echo '</pre>';
		});
	});

	$app->run(function($conf) {
		echo 'Hello World!';
		throw new AppException('This is a test!');
	});