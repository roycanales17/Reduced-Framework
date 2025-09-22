<?php

	# By Default Errors is hidden
	error_reporting(0);
	ini_set('display_errors', 0);

	# Class Importer
	spl_autoload_register(function ($class) {
		$path = '../' . lcfirst(str_replace('\\', '/', $class)) . '.php';
		if (file_exists($path)) {
			require_once $path;
		}
	});

	# Built-In Functions
	require_once '../vendor/autoload.php';
	require_once '../app/Kernel.php';
