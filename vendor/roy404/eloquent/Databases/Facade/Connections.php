<?php

	namespace App\Databases\Facade;

	use App\Databases\Handler\Blueprints\QueryReturnType;
	use App\Databases\Handler\DatabaseException;

	/**
	 * Class Connections
	 *
	 * Provides a centralized registry and manager for database servers and their
	 * active connections. Supports multiple drivers (e.g., MYSQLI, PDO) and ensures
	 * that connections are reused instead of repeatedly established.
	 *
	 * @package databases\Facade
	 */
	class Connections
	{
		/**
		 * Registered database server configurations.
		 *
		 * @var array<string, array<string, mixed>>
		 */
		private static array $configurations = [];

		/**
		 * Active database connections by server and driver.
		 *
		 * @var array<string, array<string, object>>
		 */
		private static array $connections = [];

		/**
		 * Register a new server configuration.
		 *
		 * @param string $server       The server name (key).
		 * @param array  $connection   The connection configuration array.
		 *
		 * @return void
		 */
		protected static function register(string $server, array $connection): void
		{
			self::$configurations[strtolower($server)] = $connection;
		}

		/**
		 * Check if a server exists in the registry.
		 *
		 * @param string $server      Server name.
		 * @param bool   $throwError  Whether to throw if the server does not exist.
		 *
		 * @return bool True if server exists, false otherwise.
		 *
		 * @throws DatabaseException If $throwError is true and the server is not registered.
		 */
		protected static function isServerExist(string $server, bool $throwError = false): bool
		{
			$server = strtolower($server);
			$isExist = array_key_exists($server, self::$configurations);

			if ($throwError && !$isExist) {
				throw new DatabaseException("Server '$server' is not registered");
			}

			return $isExist;
		}

		/**
		 * Get or create a database connection instance for a server.
		 *
		 * @param string|null $server   The server name. If null, a random server is chosen.
		 * @param Driver      $driver   Database driver to use (default MYSQLI).
		 *
		 * @return object The connection instance.
		 *
		 * @throws DatabaseException If no servers are registered or server does not exist.
		 */
		protected static function instance(null|string $server, Driver $driver = Driver::MYSQLI): object
		{
			if (is_null($server)) {
				if (empty(self::$configurations)) {
					throw new DatabaseException("No servers registered.");
				}
				$server = (string) array_rand(self::$configurations);
			}

			self::isServerExist($server = strtolower($server), true);

			if (isset(self::$connections[$server][$driver->value])) {
				return self::$connections[$server][$driver->value];
			}

			$connection = self::$configurations[$server];
			$object = $driver->connect($connection);

			return self::$connections[$server][$driver->value] = $object;
		}

		/**
		 * Execute a query on a given server or connection instance.
		 *
		 * If parameters are provided, PDO is chosen automatically to support
		 * prepared statements. Otherwise, MYSQLI is used.
		 *
		 * @param object|string|null   $serverOrInstance A server name, connection instance, or null for random.
		 * @param string               $query            SQL query string.
		 * @param array<string,mixed>  $parameters       Query parameters for prepared statements.
		 * @param QueryReturnType      $returnType       Expected return type.
		 *
		 * @return mixed The query result (rows, count, last insert ID, etc.).
		 *
		 * @throws DatabaseException If no servers are registered or execution fails.
		 */
		public static function execute(
			object|string|null $serverOrInstance,
			string $query,
			array $parameters = [],
			QueryReturnType $returnType = QueryReturnType::ALL
		): mixed {
			$driver = $parameters ? Driver::PDO : Driver::MYSQLI;

			if (is_string($serverOrInstance) || is_null($serverOrInstance)) {
				$serverOrInstance = self::instance($serverOrInstance, $driver);
			}

			if ($returnType === QueryReturnType::ALL) {
				$type = strtoupper(strtok(trim($query), ' '));
				$returnType = match ($type) {
					'INSERT' => QueryReturnType::LAST_INSERT_ID,
					'UPDATE', 'DELETE' => QueryReturnType::ROW_COUNT,
					'SELECT', 'SHOW', 'DESCRIBE' => QueryReturnType::ALL,
					'ALTER', 'DROP', 'RENAME', 'CREATE' => QueryReturnType::ROW_COUNT,
					default => QueryReturnType::ALL,
				};
			}

			return $driver->execute($serverOrInstance, $query, $parameters, $returnType);
		}
	}