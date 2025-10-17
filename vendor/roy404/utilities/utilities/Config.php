<?php

	namespace App\Utilities;

	/**
	 * Class Config
	 *
	 * A utility class for loading and accessing configuration values.
	 */
	final class Config
	{
		/**
		 * The configuration data loaded from a file.
		 *
		 * @var array<string, mixed>
		 */
		protected static array $config = [];

		/**
		 * Checks if the configuration is empty.
		 *
		 * @return bool True if configuration is empty, false otherwise.
		 */
		public static function isEmpty(): bool {
			return empty(self::$config);
		}

		/**
		 * Loads configuration from a .env-style file.
		 * Lines starting with '#' are treated as comments and ignored.
		 * Supports ${VAR_NAME} substitution using already loaded values.
		 *
		 * @param string $path The full path to the config file.
		 * @return void
		 */
		public static function load(string $path): void
		{
			if (!file_exists($path)) {
				return;
			}

			$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			foreach ($lines as $line) {
				$line = trim($line);
				if ($line === '' || str_starts_with($line, '#')) {
					continue;
				}

				[$key, $value] = array_pad(explode('=', $line, 2), 2, null);
				if ($key === null || $value === null) {
					continue;
				}

				$key = trim($key);
				$value = trim($value);

				// Handle quoted values
				if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
					(str_starts_with($value, "'") && str_ends_with($value, "'"))) {
					$value = substr($value, 1, -1);
				}

				// Perform variable substitution like ${VAR}
				$value = preg_replace_callback('/\$\{([A-Z0-9_]+)\}/i', function ($matches) use (&$config) {
					$var = $matches[1];
					return self::$config[$var] ?? getenv($var) ?? '';
				}, $value);

				self::$config[$key] = $value;
			}
		}

		/**
		 * Retrieves a configuration value using dot notation.
		 * Supports casting from string to boolean, number, null, or JSON if applicable.
		 *
		 * @param string $key     The configuration key (e.g., "app.debug").
		 * @param mixed  $default The default value to return if key is not found.
		 * @return mixed The configuration value or the default.
		 */
		public static function get(string $key, mixed $default = null): mixed
		{
			$keys = explode('.', $key);
			$value = self::$config;

			foreach ($keys as $keyPart) {
				if (!is_array($value) || !array_key_exists($keyPart, $value)) {
					return $default;
				}
				$value = $value[$keyPart];
			}

			return self::castValue($value);
		}

		/**
		 * Sets a configuration value using dot notation.
		 *
		 * @param string $key   The configuration key (e.g., "database.host").
		 * @param mixed  $value The value to set.
		 * @return void
		 */
		public static function set(string $key, mixed $value): void
		{
			$keys = explode('.', $key);
			$config =& self::$config;

			foreach ($keys as $keyPart) {
				if (!isset($config[$keyPart]) || !is_array($config[$keyPart])) {
					$config[$keyPart] = [];
				}
				$config =& $config[$keyPart];
			}

			$config = $value;
		}

		/**
		 * Attempts to cast a string value into an appropriate native type:
		 * - JSON arrays/objects
		 * - Boolean
		 * - Null
		 * - Integer or Float
		 *
		 * @param mixed $value The value to cast.
		 * @return mixed The cast value.
		 */
		protected static function castValue(mixed $value): mixed
		{
			if (!is_string($value))
				return $value;

			$value = trim($value);

			if ((str_starts_with($value, '{') && str_ends_with($value, '}')) ||
				(str_starts_with($value, '[') && str_ends_with($value, ']'))) {
				$json = json_decode($value, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					return $json;
				}
			}

			$lower = strtolower($value);

			return match ($lower) {
				'true'  => true,
				'false' => false,
				'null'  => null,
				default => (is_numeric($value) ? (
				str_contains($value, '.') ? (float) $value : (int) $value
				) : $value),
			};
		}
	}
