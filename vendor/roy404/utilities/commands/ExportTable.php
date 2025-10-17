<?php

	namespace Commands;

	use App\Console\Command;
    use App\Databases\Schema;

    class ExportTable extends Command {

		protected string $signature = 'schema:table';
        protected string $description = 'Export the CREATE TABLE statement (DDL) for a specific database table.';

		public function handle(string $table = ''): void
		{
            if (empty($table)) {
                $this->error("You must provide a table name.");
                return;
            }

            $this->info("Exporting schema for table: {$table}");

            $result = Schema::exportTable($table);
            $dump = $result['Create Table'] ?? null;

            if (!$dump) {
                $this->error("Failed to export schema. Table '{$table}' may not exist.");
                return;
            }

            $this->info("\n===== BEGIN SCHEMA =====\n");
            $this->info($dump);
            $this->info("\n===== END SCHEMA =====\n");
            $this->success("Schema export completed successfully.");
		}
	}
