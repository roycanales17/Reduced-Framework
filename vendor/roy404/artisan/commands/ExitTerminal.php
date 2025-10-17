<?php

	namespace Commands;

	use App\Console\Command;

	class ExitTerminal extends Command
	{
		protected string $signature = 'exit';
		protected string $description = 'Exit the application terminal';

		public function handle(): void
		{
			$this->info('⏳ Terminating the application...');
			$this->success('Application terminated successfully.');
			exit();
		}
	}
