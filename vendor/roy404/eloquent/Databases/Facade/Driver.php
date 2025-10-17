<?php

	namespace App\Databases\Facade;

	use App\Databases\Handler\Blueprints\QueryReturnType;
	use App\Databases\Handler\DatabaseException;
	use mysqli;
	use mysqli_result;
	use mysqli_sql_exception;
	use PDO;
	use PDOException;

	/**
	 * Enum Driver
	 *
	 * Provides a unified API for working with PDO or MySQLi
	 * connections and executing SQL queries. Wraps connection,
	 * execution, parameter binding, and error handling.
	 */
	enum Driver: string
	{
		case PDO = PDO::class;
		case MYSQLI = mysqli::class;

		/**
		 * Establish a database connection.
		 *
		 * @param array $config Connection config: host, port, database, username, password, charset, unix_socket
		 * @return PDO|mysqli
		 * @throws DatabaseException
		 */
		public function connect(array $config): PDO|mysqli
		{
			return match ($this) {
				self::PDO    => self::createPdoConnection($config),
				self::MYSQLI => self::createMysqliConnection($config),
			};
		}

		/**
		 * Execute a query on the given database connection.
		 *
		 * @param object $instance PDO|mysqli connection instance
		 * @param string $query SQL query with placeholders
		 * @param array $params Parameters to bind
		 * @param QueryReturnType $returnType Expected return type
		 * @return mixed
		 * @throws DatabaseException
		 */
		public function execute(
			object $instance,
			string $query,
			array $params = [],
			QueryReturnType $returnType = QueryReturnType::ALL
		): mixed {
			return match ($this) {
				self::PDO    => self::executeWithPdo($instance, $query, $params, $returnType),
				self::MYSQLI => self::executeWithMysqli($instance, $query, $params, $returnType),
			};
		}

		/**
		 * Normalize configuration keys.
		 */
		private static function normalizeConfig(array $config): array
		{
			return [
				'host'       => (string)($config['host'] ?? '127.0.0.1'),
				'port'       => (int)($config['port'] ?? 3306),
				'database'   => (string)($config['database'] ?? ''),
				'username'   => (string)($config['username'] ?? 'root'),
				'password'   => (string)($config['password'] ?? ''),
				'charset'    => (string)($config['charset'] ?? 'utf8mb4'),
				'unixSocket' => $config['unix_socket'] ?? null,
			];
		}

		/**
		 * Create a PDO connection.
		 *
		 * @throws DatabaseException
		 */
		private static function createPdoConnection(array $config): PDO
		{
			$c = self::normalizeConfig($config);

			$dsn = $c['unixSocket']
				? "mysql:unix_socket={$c['unixSocket']};dbname={$c['database']};charset={$c['charset']}"
				: "mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset={$c['charset']}";

			try {
				return new PDO($dsn, $c['username'], $c['password'], [
					PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::ATTR_EMULATE_PREPARES   => false,
				]);
			} catch (PDOException $e) {
				throw new DatabaseException("PDO connection failed: " . $e->getMessage() . " Configurations: ". print_r( $c, true ), $e->getCode(), $e->getPrevious());
			}
		}

		/**
		 * Create a MySQLi connection.
		 *
		 * @throws DatabaseException
		 */
		private static function createMysqliConnection(array $config): mysqli
		{
			$c = self::normalizeConfig($config);

			try {
				$object = new mysqli(
					$c['host'],
					$c['username'],
					$c['password'],
					$c['database'],
					$c['port'],
					$c['unixSocket']
				);

				if ($object->connect_error) {
					throw new DatabaseException("MySQLi connection failed: " . $object->connect_error);
				}

				if (!$object->set_charset($c['charset'])) {
					throw new DatabaseException("Failed to set charset to '{$c['charset']}': " . $object->error);
				}
			} catch (mysqli_sql_exception $e) {
				throw new DatabaseException("MySQLi connection failed: " . $e->getMessage() . " Configurations: ". print_r( $c, true ), (int)$e->getCode(), $e->getPrevious());
			}

			return $object;
		}

		/**
		 * Execute a query with PDO.
		 *
		 * @throws DatabaseException
		 */
		private static function executeWithPdo(
			PDO $pdo,
			string $query,
			array $params = [],
			QueryReturnType $return = QueryReturnType::ALL
		): mixed {
			try {
				$stmt = $pdo->prepare($query);
				$stmt->execute($params);

				return match ($return) {
					QueryReturnType::ROW_COUNT     => $stmt->rowCount(),
					QueryReturnType::LAST_INSERT_ID => $pdo->lastInsertId(),
					QueryReturnType::COUNT          => (int) $stmt->fetchColumn(),
					QueryReturnType::ALL            => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
				};
			} catch (PDOException $exception) {
				throw new DatabaseException("PDO query failed: " . $exception->getMessage(), (int)$exception->getCode(), $exception);
			}
		}

		/**
		 * Detect MySQLi bind_param types from parameter values.
		 */
		private static function detectParamTypes(array $params): string
		{
			return implode('', array_map(function ($param) {
				return match (true) {
					is_int($param)   => 'i',
					is_float($param) => 'd',
					is_null($param),
					is_string($param) => 's',
					default           => 'b', // blob or unknown
				};
			}, $params));
		}

		/**
		 * Execute a query with MySQLi.
		 *
		 * @throws DatabaseException
		 */
		private static function executeWithMysqli(
			mysqli $mysqli,
			string $query,
			array $params = [],
			QueryReturnType $return = QueryReturnType::ALL
		): mixed {
			try {
				$res = null;

				if (!empty($params)) {
					$stmt = $mysqli->prepare($query);
					if ($stmt === false) {
						throw new DatabaseException("MySQLi prepare failed: " . $mysqli->error);
					}

					$types = self::detectParamTypes($params);
					$stmt->bind_param($types, ...$params);

					$stmt->execute();

					if ($return === QueryReturnType::ALL || $return === QueryReturnType::COUNT) {
						$res = $stmt->get_result();
					}

					// Free statement resources
					$stmt->close();
				} else {
					$res = $mysqli->query($query);
					if ($res === false) {
						throw new DatabaseException("MySQLi query failed: " . $mysqli->error);
					}
				}

				return match ($return) {
					QueryReturnType::ROW_COUNT      => $mysqli->affected_rows,
					QueryReturnType::LAST_INSERT_ID => $mysqli->insert_id,
					QueryReturnType::COUNT          => ($res instanceof mysqli_result) ? (int) $res->fetch_row()[0] : 0,
					QueryReturnType::ALL            => ($res instanceof mysqli_result) ? $res->fetch_all(MYSQLI_ASSOC) : [],
				};
			} catch (mysqli_sql_exception $exception) {
				throw new DatabaseException("MySQLi query failed: " . $exception->getMessage(), (int)$exception->getCode(), $exception);
			}
		}
	}
