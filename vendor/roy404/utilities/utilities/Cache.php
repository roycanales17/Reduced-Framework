<?php

	namespace App\Utilities;

	use App\Utilities\Blueprints\CacheDriver;
	use Closure;
	use Exception;
	use Memcached;
	use Redis;
	use RedisException;

	/**
	 * Class Cache
	 *
	 * Provides a simple abstraction over Redis and Memcached caching systems.
	 * Supports storing, retrieving, deleting, and clearing cache entries.
	 *
	 * @package App\Utilities
	 */
	final class Cache
	{
		/**
		 * Cache configuration data.
		 *
		 * @var array
		 */
		private static array $configured = [];

		/**
		 * The active cache driver instance (Redis or Memcached).
		 *
		 * @var Memcached|Redis|null
		 */
		private static Memcached|Redis|null $driver = null;

		/**
		 * Optional callback for deferred configuration.
		 *
		 * @var Closure|null
		 */
		private static ?Closure $callback = null;

		/**
		 * Configure the cache driver with host and port.
		 *
		 * @param CacheDriver $driver The cache driver type (Redis or Memcached).
		 * @param string $server The cache server host.
		 * @param int $port The cache server port.
		 * @return void
		 */
		public static function configure(
			CacheDriver $driver = CacheDriver::Memcached,
			string $server = '',
			int $port = 0
		): void {
			if (!$server || !$port) {
				return;
			}

			self::$configured = [
				'driver' => $driver,
				'server' => $server,
				'port' => $port
			];
		}

		/**
		 * Configure and connect to a Redis instance.
		 *
		 * @param string $host
		 * @param string $port
		 * @return void
		 * @throws Exception if Redis is not available or connection fails.
		 */
		protected static function configureRedis(string $host, string $port): void
		{
			if (!class_exists('Redis')) {
				throw new Exception("Redis extension not installed");
			}

			$redis = new Redis();
			if (!$redis->connect($host, (int) $port)) {
				throw new Exception("Could not connect to Redis server");
			}

			self::$driver = $redis;
		}

		/**
		 * Configure and connect to a Memcached instance.
		 *
		 * @param string $host
		 * @param string $port
		 * @return void
		 * @throws Exception if Memcached is not available or connection fails.
		 */
		protected static function configureMemcached(string $host, string $port): void
		{
			if (!class_exists('Memcached')) {
				throw new Exception("Memcached extension not installed");
			}

			$memcached = new Memcached();
			if (!$memcached->addServer($host, (int) $port)) {
				throw new Exception("Could not connect to Memcached server");
			}

			self::$driver = $memcached;
		}

		/**
		 * Retrieve the active cache instance.
		 *
		 * @return Memcached|Redis
		 * @throws Exception if the cache is not configured.
		 */
		protected static function cache(): Memcached|Redis
		{
			if (!self::$driver) {
				if (empty(self::$configured)) {
					throw new Exception("Cache is not configured.");
				}

				$server = self::$configured['server'];
				$port = self::$configured['port'];

				match (self::$configured['driver']) {
					CacheDriver::Redis => self::configureRedis($server, $port),
					CacheDriver::Memcached => self::configureMemcached($server, $port),
				};
			}

			return self::$driver ?? throw new Exception("Cache is not configured.");
		}

		/**
		 * Retrieve the value from cache or compute/store it if missing.
		 *
		 * @param string $key
		 * @param callable $callback
		 * @param int $expiration Expiration time in seconds
		 * @return mixed
		 * @throws Exception
		 */
		public static function remember(string $key, callable $callback, int $expiration = 60): mixed
		{
			$cache = self::cache();
			$key = "remember:$key";

			$data = $cache->get($key);
			if ($data !== false) {
				$data = $cache instanceof Redis ? unserialize($data) : $data;
				return $data['data'] ?? $data;
			}

			$value = $callback();
			self::set($key, $value, $expiration);
			return $value;
		}

		/**
		 * Check if a key exists in the cache.
		 *
		 * @param string $key
		 * @return bool
		 * @throws Exception
		 */
		public static function has(string $key): bool
		{
			return self::cache()->get($key) !== false;
		}

		/**
		 * Store a value in the cache.
		 *
		 * @param string $key
		 * @param mixed $value
		 * @param int $expiration Expiration time in seconds
		 * @return bool
		 * @throws RedisException
		 */
		public static function set(string $key, mixed $value, int $expiration = 0): bool
		{
			$cache = self::cache();
			$data = [
				'data' => $value,
				'expires_at' => time() + $expiration,
			];

			return $cache instanceof Redis
				? $cache->setEx($key, $expiration, serialize($data))
				: $cache->set($key, $data, $expiration);
		}

		/**
		 * Retrieve a value from cache or return a default.
		 *
		 * @param string $key
		 * @param mixed $default
		 * @return mixed
		 */
		public static function get(string $key, mixed $default = false): mixed
		{
			$cache = self::cache();
			$data = $cache->get($key);
			if ($data === false) {
				return $default;
			}

			$data = $cache instanceof Redis ? unserialize($data) : $data;
			return $data['data'] ?? $default;
		}

		/**
		 * Delete a key from the cache.
		 *
		 * @param string $key
		 * @return bool
		 * @throws RedisException
		 */
		public static function delete(string $key): bool
		{
			$cache = self::cache();
			return $cache instanceof Redis
				? $cache->del($key)
				: $cache->delete($key);
		}

		/**
		 * Clear all entries from the cache.
		 *
		 * @return bool
		 * @throws RedisException
		 */
		public static function clear(): bool
		{
			$cache = self::cache();

			return match (true) {
				$cache instanceof Memcached => $cache->flush(),
				$cache instanceof Redis => $cache->flushAll(),
				default => false,
			};
		}

		/**
		 * Get the expiration timestamp of a cached item.
		 *
		 * @param string $key
		 * @return mixed Unix timestamp or false if not found.
		 * @throws RedisException
		 */
		public static function getExpiration(string $key): mixed
		{
			$cache = self::cache();
			$data = $cache->get($key);

			if ($data === false) {
				return false;
			}

			$data = $cache instanceof Redis ? unserialize($data) : $data;
			return $data['expires_at'] ?? false;
		}
	}
