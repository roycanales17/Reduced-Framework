<?php

	namespace App\Routes\Scheme;

	use InvalidArgumentException;
	use ReflectionException;
	use ReflectionMethod;

	/**
	 * Class Pal
	 *
	 * Handles route schemes, global domain and prefix registration, and reflection-based utilities.
	 */
	final class Pal
	{
		/**
		 * Cached route filenames per type.
		 * @var array<string, array>
		 */
		private static array $routes = [];

		/**
		 * Global route prefix.
		 */
		private static string $prefix = '';

		/**
		 * Global domain name.
		 */
		private static string $domain = '';

		/**
		 * Cached ReflectionMethod instances.
		 * @var array<string, ReflectionMethod>
		 */
		private static array $methodCache = [];

		/**
		 * Indicates if route initialization (e.g. naming) is currently in progress.
		 *
		 * @var bool
		 */
		private static bool $routeInitializing = false;

		/**
		 * Enables or disables route initialization state.
		 *
		 * This is useful when assigning names or performing actions that should
		 * only occur during route setup.
		 *
		 * @param bool $status True to enable, false to disable.
		 * @return void
		 */
		public static function toggleInitializing(bool $status): void
		{
			self::$routeInitializing = $status;
		}

		/**
		 * Checks if route initialization is currently active.
		 *
		 * @return bool True if initializing, otherwise false.
		 */
		public static function isInitializing(): bool
		{
			return self::$routeInitializing;
		}

		/**
		 * Registers a global prefix used for routing paths.
		 *
		 * @param string $prefix
		 * @return void
		 */
		public static function registerGlobalPrefix(string $prefix): void
		{
			self::$prefix = trim(str_replace('.', '/', $prefix), '/');
		}

		/**
		 * Registers a global domain. Throws an exception if invalid.
		 *
		 * @param string $domain
		 * @return void
		 * @throws InvalidArgumentException
		 */
		public static function registerGlobalDomain(string $domain): void
		{
			$domain = preg_replace('#^https?://#', '', $domain);
			$domain = preg_replace('#^www\.#', '', $domain);
			$domain = explode('/', $domain)[0];
			$domain = explode(':', $domain)[0];

			$validTld = preg_match('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i', $domain);
			$allowedLocal = in_array($domain, ['localhost', '127.0.0.1']);

			if (!filter_var('http://' . $domain, FILTER_VALIDATE_URL) || (!$validTld && !$allowedLocal)) {
				throw new InvalidArgumentException("Invalid domain: {$domain}");
			}

			self::$domain = $domain;
		}

		/**
		 * Performs a call to a private or protected method using Reflection.
		 *
		 * @param object $instance
		 * @param string $methodName
		 * @param mixed ...$params
		 * @return object|null
		 */
		public static function performPrivateMethod(object $instance, string $methodName, ...$params): ?object
		{
			$className = get_class($instance);
			$cacheKey = $className . '::' . $methodName;

			if (!isset(self::$methodCache[$cacheKey])) {
				if (!method_exists($instance, $methodName)) {
					return null;
				}

				$reflection = new ReflectionMethod($instance, $methodName);
				$reflection->setAccessible(true);
				self::$methodCache[$cacheKey] = $reflection;
			}

			return self::$methodCache[$cacheKey]->invoke($instance, ...$params);
		}

		/**
		 * Checks if a class method is static, with caching.
		 *
		 * @param string $className
		 * @param string $methodName
		 * @return bool
		 */
		public static function checkIfMethodIsStatic(string $className, string $methodName): bool
		{
			static $cache = [];

			$cacheKey = $className . '::' . $methodName;

			if (!isset($cache[$cacheKey])) {
				try {
					$reflectionMethod = new ReflectionMethod($className, $methodName);
					$cache[$cacheKey] = $reflectionMethod->isStatic();
				} catch (ReflectionException) {
					$cache[$cacheKey] = false;
				}
			}

			return $cache[$cacheKey];
		}

		/**
		 * Gets the registered global prefix.
		 *
		 * @return string
		 */
		public static function getGlobalPrefix(): string
		{
			return self::$prefix;
		}

		/**
		 * Gets the registered global domain.
		 *
		 * @return string
		 */
		public static function getGlobalDomain(): string
		{
			return self::$domain;
		}

		/**
		 * Retrieves a list of route builder files for a given type.
		 *
		 * @param string $type
		 * @return array<string>
		 */
		public static function getRoutes(string $type): array
		{
			if (isset(self::$routes[$type])) {
				return self::$routes[$type];
			}

			$baseDir = str_contains(__DIR__, '/vendor/')
				? dirname(__DIR__)
				: getcwd() . DIRECTORY_SEPARATOR . 'src';

			$path = $baseDir . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . 'builder';

			if (is_dir($path)) {
				foreach (scandir($path) ?: [] as $file) {
					if (
						$file !== '.' &&
						$file !== '..' &&
						pathinfo($file, PATHINFO_EXTENSION) === 'php'
					) {
						self::$routes[$type][] = strtolower(pathinfo($file, PATHINFO_FILENAME));
					}
				}
			}

			return self::$routes[$type] ?? [];
		}

		/**
		 * Attempts to instantiate a class with optional parameters.
		 *
		 * @param string $className
		 * @param mixed ...$params
		 * @return object|null
		 */
		public static function createInstance(string $className, ...$params): ?object
		{
			if (class_exists($className)) {
				return new $className(...$params);
			}

			return null;
		}

		/**
		 * Extracts the base class name from a fully qualified class name.
		 *
		 * @param string $className
		 * @return string
		 */
		public static function baseClassName(string $className): string
		{
			return basename(str_replace('\\', '/', $className));
		}
	}