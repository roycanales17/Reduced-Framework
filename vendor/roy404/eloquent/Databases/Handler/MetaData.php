<?php

	namespace App\Databases\Handler;

	use App\Databases\Eloquent;

	/**
	 * Class MetaData
	 *
	 * A lightweight metadata cache for single database rows,
	 * identified by table and ID. Once loaded, the row is stored
	 * in memory and can be accessed via property getters or array form.
	 *
	 * Provides automatic caching, invalidation, and lazy loading.
	 *
	 * Example usage:
	 * ```php
	 * $user = MetaData::instance(5, 'users');
	 * echo $user->name; // Accesses the "name" column
	 *
	 * $user->invalidate(); // Refreshes the cache from the database
	 * $data = $user->data(); // Get all fields as an array
	 * ```
	 *
	 * @package Databases\Handler
	 */
	final class MetaData
	{
		/**
		 * Cache of MetaData instances, indexed by table + ID.
		 *
		 * @var array<string, array<int, self>>
		 */
		private static array $instance = [];

		/**
		 * The row data as an associative array.
		 *
		 * @var array<string, mixed>
		 */
		private array $data = [];

		/**
		 * Whether this instance data has been cached.
		 *
		 * @var bool
		 */
		private bool $cachedData = false;

		/**
		 * The table name (lowercased).
		 *
		 * @var string
		 */
		private string $table;

		/**
		 * The primary key ID.
		 *
		 * @var int
		 */
		private int $id;

		/**
		 * Table column primary key.
		 *
		 * @var string
		 */
		private string $primaryKey;

		/**
		 * Database server.
		 *
		 * @var string
		 */
		private string $server;

		/**
		 * Get (or create) a cached instance for the given table and ID.
		 *
		 * @param int $id The primary key value.
		 * @param string $table The table name.
		 * @param string $primaryKey Table column primary key.
		 * @param string $server Database server.
		 * @return self
		 */
		public static function instance(int $id, string $table, string $primaryKey, string $server): self
		{
			$table = strtolower($table);
			if (!isset(self::$instance[$table][$id])) {
				self::$instance[$table][$id] = new self($id, $table, $primaryKey, $server);
			}

			return self::$instance[$table][$id];
		}

		/**
		 * Private constructor.
		 *
		 * @param int    $id    The primary key value.
		 * @param string $table The table name.
		 * @param string $primaryKey Table primary key.
		 * @param string $server Database server.
		 */
		private function __construct(int $id, string $table, string $primaryKey, string $server)
		{
			$this->id = $id;
			$this->table = strtolower($table);
			$this->primaryKey = $primaryKey;
			$this->server = $server;
		}

		/**
		 *  Check whether the database row exists.
		 *
		 *  This method verifies if the queried record actually exists in the database.
		 *  It returns `true` if the record was successfully loaded (i.e., `$this->data` is not empty),
		 *  otherwise returns `false`.
		 *
		 * @return bool
		 */
		public function isExist(): bool
		{
			$obj = new Eloquent($this->server);
			$obj->table($this->table);
			$obj->where($this->primaryKey, $this->id);

			return $obj->exists();
		}

		/**
		 * Invalidate (refresh) the cached instance for this table + ID.
		 * Re-fetches the latest data from the database and updates the cache.
		 *
		 * @return void
		 */
		public function invalidate(): void
		{
			$this->cachedData = false;
			$this->data = [];
		}

		/**
		 * Get all metadata fields as an associative array.
		 *
		 * @return array<string, mixed>
		 */
		public function data(): array
		{
			if (!$this->cachedData) {
				$obj = new Eloquent($this->server);
				$obj->table($this->table);
				$obj->where($this->primaryKey, $this->id);
				$obj->limit(1);

				foreach ($obj->row() as $key => $value) {
					$this->data[$key] = $value;
				}
			}

			$this->cachedData = true;
			return $this->data;
		}

		/**
		 * Magic getter for accessing individual fields.
		 *
		 * Example:
		 * ```php
		 * $user = MetaData::instance(5, 'users');
		 * echo $user->email;
		 * ```
		 *
		 * @param string $name The field/column name.
		 * @return mixed The field value, or empty string if not set.
		 */
		public function __get(string $name)
		{
			if (!isset($this->data[$name])) {
				$obj = new Eloquent($this->server);
				$obj->table($this->table);
				$obj->select($name);
				$obj->where($this->primaryKey, $this->id);
				$this->data[$name] = $obj->field();
			}

			return $this->data[$name] ?? null;
		}
	}