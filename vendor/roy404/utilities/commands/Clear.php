<?php

	namespace Commands;

	use App\Console\Command;
	use App\Console\Terminal;

	class Clear extends Command
	{
		protected string $signature = 'clear';
		protected string $description = 'Clear the terminal screen.';

		public function handle(): void
		{
			if (DIRECTORY_SEPARATOR === '\\') {
				system('cls');
			} else {
				system('clear');
			}

			Terminal::handle('list');
		}
	}
