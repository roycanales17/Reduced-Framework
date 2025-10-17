<?php

	namespace Commands;

	use App\Console\Command;

	class Serve extends Command
	{
		protected string $signature = 'serve';
		protected string $description = 'Serve the application out of maintenance mode';

		public function handle(): void
		{
			$this->info("⏳ Starting the application server...");

			$port = 8000;
			$host = 'localhost';

			$this->info("⏳ Initializing application server with port ($port)...");
			while (!$this->isPortAvailable($host, $port)) {
				$port++;
				$this->info("⏳ Initializing application server again with port ($port)...");
			}

			$root = $this->findProjectRoot() ."/public";
			if (file_exists($root)) {
				$this->success("Server running at http://{$host}:{$port}");

				passthru("php -S {$host}:{$port} -t {$root}");
				return;
			}

			$this->error("Cannot find root directory `{$root}`", false);
			$this->warn("Make sure the index file is exist on `{$root}` directory");
		}

		private function isPortAvailable(string $host, int $port): bool
		{
			$connection = @fsockopen($host, $port);
			if (is_resource($connection)) {
				fclose($connection);
				return false;
			}
			return true;
		}

		private function findProjectRoot(): string
		{
			$dir = __DIR__;

			while (!file_exists($dir . '/vendor') && dirname($dir) !== $dir) {
				$dir = dirname($dir);
			}

			return $dir;
		}
	}
