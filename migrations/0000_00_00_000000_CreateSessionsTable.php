<?php

	use App\Databases\Schema;
	use App\Databases\Handler\Blueprints\Table;
    use App\Utilities\Server;

    class CreateSessionsTable
	{
		/**
		 * Apply the migration
		 */
		public function up(): void
		{
            Schema::create('sessions', function (Table $table) {
                $table->string('id', 64);
                $table->integer('user_id')->nullable()->default(null);
                $table->text('data');
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamp('last_activity')->defaultNow()->updateNow();
                $table->timestamp('created_at')->defaultNow();

                // Index
                $table->primary('id');
                $table->index('user_id');
                $table->index('last_activity');
            });
		}

		/**
		 * Reverse the migration
		 */
		public function down(): void
		{
            Schema::dropIfExists('sessions');
		}
	}
