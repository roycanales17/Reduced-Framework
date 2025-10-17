<?php

	namespace App\Routes\Scheme;

	final class Buffer
	{
		private static array $buffered = [];
		private static array $storage = [];

		public static function register(string $config, $data): void
		{
			if (!(self::$buffered[$config] ?? [])) {
				self::$buffered[$config] = [];
			}

			self::$buffered[$config][] = $data;
		}

		public static function fetch(string $config): mixed
		{
			return self::$buffered[$config] ?? null;
		}

		public static function replace(string $config, $data): void
		{
			self::$buffered[$config] = $data;
		}

		public static function all(): array
		{
			return self::$buffered;
		}

		public static function set(string $config, $data): void
		{
			self::$storage[$config]	= $data;
		}

		public static function get(string $config): mixed
		{
			return self::$storage[$config] ?? null;
		}
	}