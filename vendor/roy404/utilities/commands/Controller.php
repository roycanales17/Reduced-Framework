<?php

	namespace Commands;

	use App\Console\Command;

	class Controller extends Command
	{
		protected string $signature = 'make:controller';
		protected string $description = 'Generates controller class.';

		public function handle(string $className = ''): void
		{
			if (empty($className)) {
				$this->error('Controller class name is required.');
				return;
			}

			$this->info('⏳ Initializing controller class file generation...');

			$className = preg_replace('/[^A-Za-z0-9_\/]/', '', "Http/Controller/$className");
			$directories = explode('/', $className);
			$className = ucfirst($directories[count($directories) - 1]);

			array_pop($directories);
			$basePath = base_path("/" . implode('/', $directories));
			$namespaceDeclaration = "namespace ". implode('\\', array_map('ucfirst', $directories)) . ";";

			$content = <<<PHP
			<?php
			
				{$namespaceDeclaration}
			
				use App\Http\Controller;
				
				class {$className} extends Controller
				{
					public function index() {
					
					}
					
					public function edit() {
					
					}
					
					public function delete() {
					
					}
				}
			PHP;

			// Create the PHP file
			if ($this->create("$className.php", $content, $basePath)) {
				$this->success("✅ Controller class file '{$className}' has been successfully created and is ready for use.");
			} else {
				$this->error("❌ Failed to create the file '{$className}.php' at '{$basePath}'.");
			}
		}
	}
