<?php

	namespace Commands;
	
	use App\Console\Command;
	use App\Databases\Schema;
	use App\Utilities\Server;
	use App\Databases\Handler\Blueprints\Table;

	class SessionHandler extends Command {
		
		protected string $signature = 'make:session';
		protected string $description = 'Migrate database session.';
		
		public function handle(): void
		{
			if (!Schema::hasTable('sessions')) {
				Schema::create('sessions', function (Table $table) {
					$table->text('id');
					$table->text('data');
					$table->string('ip_address', 128)->default(Server::IPAddress());
					$table->timestamp('last_activity')->defaultNow()->updateNow();
					$table->unique('id');
				});

				$this->success("Sessions table was created.");
				return;
			}

			$this->warn("Sessions table already exists.");
		}
	}