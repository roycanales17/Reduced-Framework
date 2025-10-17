<?php

	namespace App\Databases\Handler\Eloquent;

	use App\Databases\Database;
	use App\Databases\Handler\Blueprints\QueryReturnType;

	/**
	 * Abstract Builder
	 *
	 * Provides a fluent interface for constructing and executing
	 * SQL queries such as SELECT, UPDATE, DELETE, INSERT, and REPLACE.
	 *
	 * This class is meant to be extended by specific query builders
	 * that define table names and server connections.
	 */
	abstract class Builder extends QueryResults
	{
		/** Special marker for empty values in queries. */
		const EMPTY = '__empty__';

		/** @var string Database server connection name */
		protected string $server;

		/** @var string Table name to execute queries on */
		protected string $table = '';

		/** @var array Columns to be selected in SELECT queries */
		protected array $columns = [];

		/** @var array WHERE clause conditions */
		protected array $wheres = [];

		/** @var array SET clause data for UPDATE queries */
		protected array $sets = [];

		/** @var array Bound parameters for prepared statements */
		protected array $bindings = [];

		/** @var array ORDER BY clauses */
		protected array $orders = [];

		/** @var int|null Query limit */
		protected ?int $limit = null;

		/** @var int|null Query offset */
		protected ?int $offset = null;

		/** @var string The last executed SQL statement */
		protected string $lastSql = '';

		/** @var string Current query being built */
		protected string $query = '';

		/**
		 * Build a WHERE clause from the accumulated conditions.
		 *
		 * @return string SQL WHERE clause or empty string if no conditions
		 */
		protected function buildWhere(): string {
			if (empty($this->wheres)) {
				return '';
			}

			$sql = 'WHERE ';
			$parts = [];

			foreach ($this->wheres as $where) {
				if (($where[0] ?? '') === 'nested') {
					$nestedParts = [];
					$wheres = $where[1] ?? [];
					$boolean = $where[2] ?? '';
					foreach ($wheres as [$nExpr, $nType]) {
						$nestedParts[] = ($nestedParts ? $nType . ' ' : '') . $nExpr;
					}
					$expr = '(' . implode(' ', $nestedParts) . ')';
					$parts[] = ($parts ? $boolean . ' ' : '') . $expr;
				} else {
					[$expr, $boolean] = $where;
					$parts[] = ($parts ? $boolean . ' ' : '') . $expr;
				}
			}

			return $sql . implode(' ', $parts);
		}

		/**
		 * Check if any rows match the current query.
		 *
		 * @return bool True if at least one row exists, false otherwise
		 */
		public function exists(): bool
		{
			$sql = "SELECT 1 FROM {$this->table} " . $this->buildWhere() . " LIMIT 1";

			$result = Database::server($this->server)
				->query($this->lastSql = $sql, $this->bindings)
				->field();

			return !empty($result);
		}

		/**
		 * Get the total number of rows matching the current query.
		 *
		 * @return int Number of matching rows
		 */
		public function count(): int
		{
			$sql = "SELECT COUNT(*) as total FROM {$this->table} " . $this->buildWhere();

			$row = Database::server($this->server)
				->query($this->lastSql = $sql, $this->bindings)
				->field();

			return intval( $row );
		}

		/**
		 * Execute an UPDATE statement on the current table.
		 *
		 * @return int Number of affected rows
		 */
		public function update(): int
		{
			$setClauses = [];
			foreach ($this->sets as $column => $placeholder) {
				$setClauses[] = "$column = $placeholder";
			}

			$sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . ' ' . $this->buildWhere();

			return Database::server($this->server)
				->query($this->lastSql = $sql, $this->bindings)
				->totalAffected();
		}

		/**
		 * Execute a DELETE statement on the current table.
		 *
		 * @return int Number of affected rows
		 */
		public function delete(): mixed
		{
			$sql = "DELETE FROM {$this->table} " . $this->buildWhere();

			return Database::server($this->server)
				->query($this->lastSql = $sql, $this->bindings)
				->totalAffected();
		}

		/**
		 * Execute a REPLACE INTO statement.
		 *
		 * @param array $data Key-value pairs of column => value
		 * @param array $fillable Optional whitelist of allowed columns
		 * @param QueryReturnType $returnType What to return (row count or last insert ID)
		 * @return mixed Row count or last inserted ID
		 */
		public function replace(array $data, array $fillable = [], QueryReturnType $returnType = QueryReturnType::ROW_COUNT): mixed
		{
			if (!empty($fillable)) {
				$data = array_intersect_key($data, array_flip($fillable));
			}

			$columns = array_keys($data);
			$placeholders = implode(',', array_map(fn($c) => ":$c", $columns));
			$sql = "REPLACE INTO {$this->table} (" . implode(',', $columns) . ") VALUES ($placeholders)";

			$bindings = [];
			foreach ($data as $key => $value) {
				$bindings[":$key"] = $value;
			}

			if ($returnType == QueryReturnType::ROW_COUNT) {
				return Database::server($this->server)
					->query($this->lastSql = $sql, $bindings)
					->totalAffected();
			}

			return Database::server($this->server)
				->query($this->lastSql, $bindings)
				->lastInsertedID();
		}

		/**
		 * Execute an INSERT INTO statement.
		 *
		 * @param array $data Key-value pairs of column => value
		 * @param array $fillable Optional whitelist of allowed columns
		 * @return int Last inserted ID
		 */
		public function create(array $data, array $fillable = []): mixed
		{
			if (!empty($fillable)) {
				$data = array_intersect_key($data, array_flip($fillable));
			}

			$columns = array_keys($data);
			$placeholders = implode(',', array_map(fn($c) => ":$c", $columns));
			$sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES ($placeholders)";

			$bindings = [];
			foreach ($data as $key => $value) {
				$bindings[":$key"] = $value;
			}

			return Database::server($this->server)
				->query($this->lastSql = $sql, $bindings)
				->lastInsertedID();
		}

		/**
		 * Get the raw SQL string from the current query.
		 *
		 * @param bool $interpolate Whether to replace bindings with values
		 * @return string Raw SQL string
		 */
		public function rawSQL(bool $interpolate = true): string {
			if (empty($this->lastSql)) {
				$cols = $this->columns ?: ['*'];
				$sql = "SELECT " . implode(', ', $cols) . " FROM {$this->table} " . $this->buildWhere();
			} else {
				$sql = $this->lastSql;
			}

			$raw = $sql;
			if ($interpolate) {
				foreach ($this->bindings as $param => $value) {
					$quoted = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
					if (is_string($param)) {
						$raw = str_replace($param, $quoted, $raw);
					} else {
						$raw = preg_replace('/\?/', $quoted, $raw, 1);
					}
				}
			}

			return $raw;
		}
	}