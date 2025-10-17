<?php

	namespace Commands;
	
	use App\Console\Command;
	use FilesystemIterator;

	class ClearDirectory extends Command {
		
		protected string $signature = 'clear:directory';
		protected string $description = 'Clear the directory files and subdirectories.';
		
		public function handle(string $path = ''): void
		{
			if (!$path) {
				$this->error("Path is required.");
				return;
			}

			$targetPath = base_path("/$path");

			if (!is_dir($targetPath)) {
				$this->warn("Directory `$targetPath` does not exist or is not a directory.");
				return;
			}

			$items = new FilesystemIterator($targetPath, FilesystemIterator::SKIP_DOTS);

			foreach ($items as $item) {
				if ($item->isDir()) {
					$this->deleteDirectory($item->getPathname());
				} else {
					unlink($item->getPathname());
				}
			}

			$this->info("Cleared contents of `$targetPath`.");
		}

		private function deleteDirectory(string $dir): void
		{
			if (!is_dir($dir)) return;

			$items = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);

			foreach ($items as $item) {
				if ($item->isDir()) {
					$this->deleteDirectory($item->getPathname());
				} else {
					unlink($item->getPathname());
				}
			}

			rmdir($dir);
		}
	}