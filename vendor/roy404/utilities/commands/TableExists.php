<?php

    namespace Commands;

    use App\Console\Command;
    use App\Databases\Schema;

    class TableExists extends Command {

        protected string $signature = 'schema:exists';
        protected string $description = 'Check if a given database table exists.';

        public function handle(string $table = ''): void
        {
            if (empty($table)) {
                $this->error("You must provide a table name.");
                return;
            }

            $this->info("Checking if table '{$table}' exists...");
            $result = Schema::hasTable($table);

            if ($result) {
                $this->success("Table '{$table}' exists in the database.");
            } else {
                $this->error("Table '{$table}' does not exist.");
            }
        }
    }
