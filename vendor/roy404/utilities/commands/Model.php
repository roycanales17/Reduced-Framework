<?php

	namespace Commands;

	use App\Console\Command;

	class Model extends Command
	{
		protected string $signature = 'make:model';
		protected string $description = 'Generates model class.';

		public function handle(string $className = ''): void
		{
			if (empty($className)) {
				$this->error('Model class name is required.');
				return;
			}

			$this->info('⏳ Initializing model class file generation...');

			$className = preg_replace('/[^A-Za-z0-9_\/]/', '', "http/Model/$className");
			$directories = explode('/', $className);
			$className = ucfirst($directories[count($directories) - 1]);

			array_pop($directories);
			$basePath = base_path("/" . implode('/', $directories));
			$namespaceDeclaration = "namespace ". implode('\\', array_map('ucfirst', $directories)) . ";";

			$table = strtolower($className);
			$content = <<<PHP
			<?php
			
				{$namespaceDeclaration}
			
				use App\Databases\Facade\Model;
				
				class {$className} extends Model
				{
					public string \$primary_key = 'id';
					public string \$table = '{$table}';
					public array \$fillable = [];
				}
			PHP;

			// Create the PHP file
			if ($this->create("$className.php", $content, $basePath)) {
				$this->success("✅ Model class file '{$className}' has been successfully created and is ready for use.");
			} else {
				$this->error("❌ Failed to create the file '{$className}.php' at '{$basePath}'.");
			}
		}
	}
