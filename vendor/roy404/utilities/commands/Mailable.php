<?php

	namespace Commands;
	
	use App\Console\Command;
	
	class Mailable extends Command {
		
		protected string $signature = 'make:mail';
		protected string $description = 'Generate a new mailable class.';
		
		public function handle(string $className = ''): void
		{
			if (!$className) {
				$this->error('Mail class name is required.');
				return;
			}

			$this->info('⏳ Initializing mail class file generation...');

			$className = preg_replace('/[^A-Za-z0-9_]/', '', $className);
			$className = ucfirst($className);

			$filename = $className . '.php';
			$content = <<<HTML
			<?php

				namespace Mails;
				
				use App\Utilities\Handler\Mailable;
				
				class {$className} extends Mailable {
				
					public array \$data;

					public function __construct(array \$data)
					{
						\$this->data = \$data;
					}
			
					public function send(): bool
					{
						return \$this->view('welcome', \$this->data)->build();
					}
				}
			HTML;

			if ($this->create($filename, $content, base_path('/mails'))) {
				$this->success("✅ Mail class '{$filename}' has been successfully created and is ready for use.");
				return;
			}

			$this->error("❌ Failed to create the file '{$filename}'.");
		}
	}