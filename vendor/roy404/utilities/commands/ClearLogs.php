<?php

	namespace Commands;

	use App\Console\Command;

	class ClearLogs extends Command
	{
		protected string $signature = 'clear:logs';
		protected string $description = 'Flush the application logs.';

		public function handle(bool $force = false): void
		{
			if (!$force) {
				$confirm = $this->confirm("Are you sure you want to clear logs?");
				if (!$confirm) {
					$this->info("Clearing logs is cancelled \n");
					return;
				}
			}

			$this->info('Clearing application logs...');
			$path = base_path('/logs');

			if (is_dir($path)) {
				$this->deleteDirectory($path);
				$this->success('Application logs cleared successfully.');
			} else {
				$this->success('No log directory found to clear.');
			}
		}

		private function deleteDirectory(string $dir): void
		{
			$items = array_diff(scandir($dir), ['.', '..']);
			foreach ($items as $item) {
				$itemPath = "$dir/$item";
				if (is_dir($itemPath)) {
					$this->deleteDirectory($itemPath);
				} else {
					if (unlink($itemPath)) {
						$this->info("Deleted file: $itemPath");
					} else {
						$this->warn("Failed to delete file: $itemPath");
					}
				}
			}

			if (rmdir($dir)) {
				$this->info("Deleted directory: $dir");
			} else {
				$this->warn("Failed to delete directory: $dir");
			}
		}
	}
