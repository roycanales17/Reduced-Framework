<?php

	namespace Commands;

	use App\Bootstrap\Exceptions\AppException;
	use App\Console\Command;
	use App\Console\Schedule;

	class Cron extends Command {

		protected string $signature = 'cron:scheduler';
		protected string $description = 'Runs the application scheduler every minute via cron.';

		public function handle(): void
		{
			$artisan = config('ARTISAN_FILENAME', 'artisan');
			$artisanPath = base_path("/{$artisan}");
			$routePath = Schedule::getRoute();

			if (!file_exists($routePath)) {
				throw new AppException("[Cron] Route file not found: {$routePath}");
			}

			if (!file_exists($artisanPath)) {
				throw new AppException("[Cron] Console file not found: {$artisanPath}");
			}

			require_once $routePath;
			Schedule::execute($artisanPath, true);
		}
	}
