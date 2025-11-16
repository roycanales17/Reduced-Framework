<?php

    use App\Databases\Schema;
    use App\Databases\Handler\Blueprints\Table;

    final class CreateUsersTable
    {
        /**
         * Apply the migration
         */
        public function up(): void
        {
            Schema::create('users', function (Table $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('email', 150);
                $table->string('password');
                $table->string('role', 50)->nullable();

                // Tokens
                $table->string('remember_token', 64)->nullable();
                $table->string('reset_token', 64)->nullable();
                $table->datetime('reset_expires')->nullable();
                $table->string('api_token', 80)->nullable();
                $table->datetime('api_token_expires')->nullable();

                // Timestamps
                $table->timestamp('last_login')->nullable();
                $table->timestamp('created_at')->defaultNow();
                $table->timestamp('updated_at')->defaultNow();

                // Unique constraint
                $table->unique('email');
            });
        }

        /**
         * Reverse the migration
         */
        public function down(): void
        {
            Schema::dropIfExists('users');
        }
    }
