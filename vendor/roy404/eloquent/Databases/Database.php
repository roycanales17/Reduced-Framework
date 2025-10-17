<?php

	namespace App\Databases;

	use App\Databases\Facade\Connections;
	use App\Databases\Handler\Blueprints\ServerChain;
	use App\Databases\Handler\Blueprints\QueryReturnType;
	use App\Databases\Handler\DatabaseException;
	use App\Databases\Handler\Blueprints\UpdateChain;

	/**
	 * Class Database
	 *
	 * Provides a high-level API for configuring servers,
	 * running queries, and interacting with tables.
	 *
	 * @package Databases
	 */
	class Database extends Connections
	{
		/**
		 * Register a new database server configuration.
		 *
		 * @param string $server  The server identifier (name).
		 * @param array  $config  The connection configuration (host, user, password, database, etc).
		 *
		 * @throws DatabaseException If the server is already registered.
		 * @return void
		 */
		public static function configure(string $server, array $config): void
		{
			if (!self::isServerExist($server)) {
				self::register($server, $config);
				return;
			}

			throw new DatabaseException("Server '{$server}' is already registered");
		}

		/**
		 * Get an Actions instance for the specified server.
		 *
		 * @param string $server The server identifier.
		 *
		 * @return ServerChain
		 */
		public static function server(string $server): ServerChain
		{
			return new ServerChain($server);
		}

		/**
		 * Start an Eloquent query builder instance for a specific table.
		 *
		 * @param string $table The table name.
		 *
		 * @return Eloquent
		 */
		public static function table(string $table): Eloquent
		{
			$obj = new Eloquent();
			return $obj->table($table);
		}

		/**
		 * Execute a raw SQL query and return results.
		 *
		 * @param string          $query      The SQL query to execute.
		 * @param array           $params     Bound parameters for prepared statement.
		 * @param QueryReturnType $returnType The expected return type (e.g., ALL, FIRST, COUNT).
		 *
		 * @return mixed The query result.
		 */
		public static function query(string $query, array $params = [], QueryReturnType $returnType = QueryReturnType::ALL): mixed
		{
			return self::execute(null, $query, $params, $returnType);
		}

		/**
		 * Insert or update a row in the database using REPLACE.
		 *
		 * @param string $table The table name.
		 * @param array  $data  Associative array of column => value pairs.
		 *
		 * @return mixed The inserted/updated primary key or result.
		 */
		public static function replace(string $table, array $data): mixed
		{
			$obj = new Eloquent();
			$obj->table($table);
			return $obj->replace($data);
		}

		/**
		 * Update rows in the database using a fluent chain builder.
		 *
		 * Example:
		 * ```php
		 * Database::update('users', ['name' => 'John', 'email' => 'john@example.com'])
		 *     ->where('id', 1)
		 *     ->execute();
		 * ```
		 *
		 * @param string $table The table name.
		 * @param array  $data  Associative array of column => value pairs.
		 *
		 * @return UpdateChain Returns an UpdateChain instance for chaining conditions.
		 */
		public static function update(string $table, array $data): UpdateChain
		{
			return new UpdateChain($table, $data);
		}

		/**
		 * Delete rows from the database with given conditions.
		 *
		 * @param string $table      The table name.
		 * @param array  $conditions Associative array of conditions:
		 *                           - ['column' => 'value']
		 *                           - ['column' => ['operator', 'value']]
		 *
		 * @return int Number of affected rows.
		 */
		public static function delete(string $table, array $conditions): int
		{
			$obj = new Eloquent();
			$obj->table($table);

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
		 * Create a new row in the database.
		 *
		 * @param string $table The table name.
		 * @param array  $data  Associative array of column => value pairs.
		 *
		 * @return int The inserted row ID.
		 */
		public static function create(string $table, array $data): int
		{
			$obj = new Eloquent();
			$obj->table($table);
			return $obj->create($data);
		}
	}