<?php

	namespace App\Databases;

	use App\Databases\Handler\Blueprints\Table;
	use App\Databases\Handler\Collection;
	use Closure;

	class Schema
	{
		public static function create(string $table, Closure $callback)
		{
			$blueprint = new Table($table);
			$callback($blueprint);

			$sql = $blueprint->toSql('create');
			if ($sql) {
				return Database::query($sql);
			}

			return false;
		}

		public static function table(string $table, Closure $callback)
		{
			$blueprint = new Table($table);
			$callback($blueprint);

			$sql = $blueprint->toSql('alter');
			if ($sql) {
				return Database::query($sql);
			}

			return false;
		}

		public static function renameTable(string $from, string $to)
		{;
			return Database::query("ALTER TABLE {$from} RENAME TO {$to}");
		}

		public static function dropIfExists(string $table)
		{
			return Database::query("DROP TABLE IF EXISTS {$table}");
		}

		public static function hasTable(string $table): int
		{
			$result = Database::query("SHOW TABLES LIKE '{$table}'");
			$obj = new Collection($result);

			return $obj->count(true);
		}

		public static function column(string $table, string $column): array
		{
			$result = Database::query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
			$obj = new Collection($result);

			return $obj->row();
		}

		public static function columns(string $table, array $columns)
		{
			$cols = implode("','", $columns);
			return Database::query("SHOW COLUMNS FROM {$table} WHERE Field IN ('{$cols}')");
		}

		public static function fetchColumns(string $table): array
		{
			return Database::query("SHOW COLUMNS FROM `{$table}`");
		}

		public static function exportTable(string $table): array
		{
			$result = Database::query("SHOW CREATE TABLE `{$table}`");
			$obj = new Collection($result);
			return $obj->row();
		}

		public static function index(string $table, string $indexName): mixed
		{
			$result = Database::query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
			$obj = new Collection($result);
			return $obj->row();
		}

		public static function drop(string $table): mixed
		{
			return Database::query("DROP TABLE `{$table}`");
		}

		public static function dropColumn(string $table, string $column): mixed
		{
			return Database::query("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
		}

		public static function renameColumn(string $table, string $from, string $to, string $type): mixed
		{
			return "ALTER TABLE `{$table}` CHANGE `{$from}` `{$to}` {$type}";
		}

		public static function addIndex(string $table, string $column, string $indexName = null): mixed
		{
			$indexName = $indexName ?? "{$column}_index";
			return Database::query("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$column}`)");
		}

		public static function dropIndex(string $table, string $indexName): mixed
		{
			return Database::query("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
		}

		public static function setEngine(string $table, string $engine): mixed
		{
			return Database::query("ALTER TABLE `{$table}` ENGINE={$engine}");
		}

		public static function setCharset(string $table, string $charset, string $collation = null): mixed
		{
			$sql = "ALTER TABLE `{$table}` DEFAULT CHARSET={$charset}";
			if ($collation) {
				$sql .= " COLLATE={$collation}";
			}
			return Database::query($sql);
		}

		public static function truncate(string $table): mixed
		{
			return Database::query("TRUNCATE TABLE `{$table}`");
		}
	}
