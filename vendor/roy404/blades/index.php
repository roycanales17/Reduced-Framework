<?php

	use App\View\Compilers\Blade;
	use App\View\Compilers\Scheme\CompilerException;

	spl_autoload_register(function ( $class) {
		$namespaces = [
			'App\\View\\Compilers' => __DIR__ . '/src/'
		];
		foreach ($namespaces as $namespace => $baseDir) {
			if (str_starts_with($class, $namespace)) {
				$relativeClass = str_replace($namespace, '', $class);
				$file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

				if (file_exists($file)) {
					require_once $file;
				}
			}
		}
	});

	// Render the content
	try {
		echo(Blade::compile(file_get_contents( 'views/home.blade.php' )));
	} catch (CompilerException $e) {
		echo($e->getMessage());
	}