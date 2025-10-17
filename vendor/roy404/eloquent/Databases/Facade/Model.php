<?php

	namespace App\Databases\Facade;

	use Closure;
	use App\Databases\Eloquent;
	use App\Databases\Handler\MetaData;
	use App\Databases\Handler\Blueprints\UpdateChain;

	/**
	 * Base Model class
	 *
	 * Provides a simple ActiveRecord-style API on top of Eloquent-like query builder.
	 * Each extending model should define:
	 *   - $table (string)        : database table name (optional, defaults to class name)
	 *   - $primary_key (string)  : primary key column (default: "id")
	 *   - $fillable (array)      : columns allowed for mass assignment
	 *   - $server (string)       : connection/server name
	 */
	abstract class Model
	{
		protected string $table = '';
		protected string $primary_key = 'id';
		protected array $fillable = [];
		protected string $server = 'master';

		/**
		 * Fetch a MetaData wrapper by primary key.
		 *
		 * @param int $id Primary key value.
		 */
		public static function _(int $id): MetaData
		{
			$instance = new static();
			$primaryKey = $instance->primary_key;
			$table = self::baseTable($instance);

			return MetaData::instance($id, $table, $primaryKey, $instance->server ?: 'master');
		}

		/**
		 * Get all rows for the model.
		 */
		public static function all(): array
		{
			$instance = new static();
			$obj = new Eloquent($instance->server);

			$obj->table(self::baseTable($instance));

			return !empty($instance->primary_key)
				? $obj->fetch()
				: [];
		}

		/**
		 * Find a row by primary key.
		 */
		public static function find(int $id): array
		{
			$instance = new static();
			$obj = new Eloquent($instance->server);

			$obj->table(self::baseTable($instance));

			if (!empty($instance->primary_key)) {
				$obj->where($instance->primary_key, $id);
				return $obj->row();
			}

			return [];
		}

		/**
		 * Start a select query with specific columns.
		 */
		public static function select(string|Closure ...$columns): Eloquent
		{
			$instance = new static();
			$obj = new Eloquent($instance->server);

			$obj->table(self::baseTable($instance));
			$obj->select(...$columns);

			return $obj;
		}

		/**
		 * Start a query with a where condition.
		 */
		public static function where(
			string|Closure $col,
			mixed $OperatorOrValue = null,
			mixed $value = Eloquent::EMPTY
		): Eloquent {
			$instance = new static();
			$obj = new Eloquent($instance->server);

			$obj->table(self::baseTable($instance));
			$obj->where(...func_get_args());

			return $obj;
		}

		/**
		 * Delete rows matching conditions.
		 *
		 * Example:
		 *   User::remove(['id' => 1]);
		 *   User::remove(['status' => ['!=', 'inactive']]);
		 */
		public static function remove(array $conditions): mixed
		{
			$instance = new static();
			$obj = new Eloquent($instance->server);

			$obj->table(self::baseTable($instance));

			foreach ($conditions as $column => $value) {
				if (is_array($value)) {
					[$operator, $val] = $value;
					$obj->where($column, $operator, $val);
				} else {
					$obj->where($column, $value);
				}
			}

			return $obj->delete();
		}

		/**
		 * Replace (INSERT ... ON DUPLICATE KEY UPDATE) values.
		 */
		public static function replace(array $values): mixed
		{
			$instance = new static();
			$obj = new Eloquent($instance->server);

			$obj->table(self::baseTable($instance));
			return $obj->replace($values, $instance->fillable);
		}

		/**
		 * Insert new row(s) with fillable enforcement.
		 */
		public static function create(array $values): mixed
		{
			$instance = new static();
			$obj = new Eloquent($instance->server);

			$obj->table(self::baseTable($instance));
			return $obj->create($values, $instance->fillable);
		}

		/**
		 * Start an update query for the model's table.
		 *
		 * @param array $values Associative array of column => value pairs to update initially.
		 *                      More values can be added later via `set()`.
		 *
		 * @return UpdateChain Fluent update builder chain for further conditions and execution.
		 */
		public static function update(array $values): UpdateChain
		{
			$instance = new static();
			return new UpdateChain(self::baseTable($instance), $values, new Eloquent($instance->server));
		}

		/**
		 * Resolve table name.
		 * Uses $table if defined, otherwise falls back to class basename (lowercased).
		 */
		private static function baseTable(?object $instance = null): string
		{
			if (is_null($instance)) {
				$instance = new static();
			}

			if (!empty($instance->table)) {
				return $instance->table;
			}

			$fullClass = get_class($instance);
			$baseClass = basename(str_replace('\\', '/', $fullClass));

			return strtolower($baseClass);
		}
	}
