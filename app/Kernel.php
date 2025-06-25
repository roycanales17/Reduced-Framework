<?php
	use App\Bootstrap\Exceptions\AppException;
	use App\Bootstrap\Handler\RuntimeException;
	use App\Bootstrap\Application;
	use App\Utilities\Logger;

	Application::boot()
	->withEnvironment('../.env')
	->withConfiguration('../app/Config.php')
	->withExceptions(function (RuntimeException $exception) {
		$exception->report(function(AppException $e) {
			$logger = new Logger('../logs', logFile: 'error.log');
			$logger->error(strip_tags($e->getMessage()), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'trace' => $e->getTraceAsString()
			]);
		});
	})->run();
