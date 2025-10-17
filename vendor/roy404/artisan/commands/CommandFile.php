<?php

	namespace Commands;

	use App\Console\Command;

	class CommandFile extends Command
	{
		protected string $signature = 'make:command';
		protected string $description = 'Generates a new command file';

		public function handle(string $className = '', string $signature = '', string $description = ''): void
		{
			if (!$className) {
				$this->error('Class name is required.');
				return;
			}

			$this->info('⏳ Initializing command file generation...');

			$className = preg_replace('/[^A-Za-z0-9_]/', '', $className);
			$className = ucfirst($className);

			if (!empty($signature)) {
				$signature = strtolower(trim($signature));
			}

			if (!empty($description)) {
				$description = trim(ucfirst($description));
			}

			$filename = $className . '.php';
			$content = <<<HTML
			<?php

				namespace Commands;
				
				use App\Console\Command;
				
				class {$className} extends Command {
					
					protected string \$signature = '{$signature}';
					protected string \$description = '{$description}';
					
					public function handle(): void
					{
						\$this->info("Executing {$className} command...");
					}
				}
			HTML;

			if ($this->create($filename, $content, dirname('./'). '/commands')) {
				$this->success("Command file '{$filename}' has been successfully created and is ready for use.");
				return;
			}

			$this->error("Failed to create the file '{$filename}'.");
		}
	}
