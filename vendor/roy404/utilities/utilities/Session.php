<?php

	namespace App\Utilities;

	use App\Utilities\Handler\DatabaseSessionHandler;
// use App\Utilities\Handler\RedisSessionHandler;

	/**
	 * Class Session
	 *
	 * Provides a static interface for configuring and managing PHP sessions.
	 *
	 * Supports file and database drivers, custom lifetime settings, flash data, and regeneration.
	 *
	 * @package App\Utilities
	 */
	final class Session
	{
		/**
		 * Configures session parameters and optionally sets a custom session handler.
		 *
		 * Supported config keys:
		 * - driver: 'file', 'database', 'redis' (default: 'file')
		 * - lifetime: in minutes (default: 120)
		 * - expire_on_close: bool (default: false)
		 * - storage_path: string for file driver
		 * - path, domain, secure, http_only, same_site: cookie parameters
		 *
		 * @param array $config Configuration options for the session.
		 * @return void
		 */
		public static function configure(array $config): void
		{
			if (session_status() === PHP_SESSION_NONE) {
				$config['driver'] = $config['driver'] ?? 'file';

				ini_set('session.gc_maxlifetime', ($config['lifetime'] ?? 120) * 60);

				if ($config['driver'] === 'file') {
					$storagePath = $config['session']['storage_path'] ?? '../storage/sessions';
					if (!is_dir($storagePath)) {
						mkdir($storagePath, 0755, true);
					}
					session_save_path($storagePath);
				}

				session_set_cookie_params([
					'lifetime' => $config['session']['expire_on_close'] ? 0 : ($config['session']['lifetime'] ?? 120) * 60,
					'path'     => $config['session']['path'] ?? '/',
					'domain'   => $config['session']['domain'] ?? '',
					'secure'   => $config['session']['secure'] ?? false,
					'httponly' => $config['session']['http_only'] ?? true,
					'samesite' => $config['session']['same_site'] ?? 'lax',
				]);

				switch ($config['driver']) {
					case 'redis':
						// session_set_save_handler(new RedisSessionHandler($object), true);
						break;

					case 'database':
						session_set_save_handler(new DatabaseSessionHandler, true);
						break;
				}
			}
		}

		/**
		 * Starts the session if it is not already started.
		 *
		 * @return void
		 */
		public static function start(): void
		{
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}
		}

		/**
		 * Checks if the session is currently active.
		 *
		 * @return bool True if session is active.
		 */
		public static function started(): bool
		{
			return session_status() === PHP_SESSION_ACTIVE;
		}

		/**
		 * Stores a value in the session.
		 *
		 * @param string $key   Session key.
		 * @param mixed  $value Value to store.
		 * @return void
		 */
		public static function set(string $key, mixed $value): void
		{
			self::start();
			$_SESSION[$key] = $value;
		}

		/**
		 * Retrieves a value from the session.
		 *
		 * @param string $key     Session key.
		 * @param mixed  $default Default value if key doesn't exist.
		 * @return mixed
		 */
		public static function get(string $key, mixed $default = false): mixed
		{
			self::start();
			return $_SESSION[$key] ?? $default;
		}

		/**
		 * Checks if a key exists in the session.
		 *
		 * @param string $key Session key.
		 * @return bool True if key exists.
		 */
		public static function has(string $key): bool
		{
			self::start();
			return isset($_SESSION[$key]);
		}

		/**
		 * Removes a key from the session.
		 *
		 * @param string $key Session key to remove.
		 * @return void
		 */
		public static function remove(string $key): void
		{
			self::start();
			if (isset($_SESSION[$key])) {
				unset($_SESSION[$key]);
			}
		}

		/**
		 * Sets or retrieves a "flash" session value.
		 *
		 * Flash data is only available for the next request.
		 *
		 * @param string $key   Flash key.
		 * @param mixed  $value Optional value to set. If not set, retrieves and deletes the value.
		 * @return mixed False when setting; the value or false when retrieving.
		 */
		public static function flash(string $key, mixed $value = false): mixed
		{
			self::start();

			if ($value !== false) {
				$_SESSION['__flash'][$key] = $value;
				return false;
			}

			$flashValue = $_SESSION['__flash'][$key] ?? false;
			if (isset($_SESSION['__flash'][$key])) {
				unset($_SESSION['__flash'][$key]);
			}

			return $flashValue;
		}

		/**
		 * Destroys the current session and clears all data.
		 *
		 * @return void
		 */
		public static function destroy(): void
		{
			if (self::started()) {
				session_destroy();
				$_SESSION = [];
			}
		}

		/**
		 * Regenerates the session ID.
		 *
		 * @param bool $deleteOldSession Whether to delete the old session data.
		 * @return void
		 */
		public static function regenerate(bool $deleteOldSession = true): void
		{
			self::start();
			session_regenerate_id($deleteOldSession);
		}
	}
