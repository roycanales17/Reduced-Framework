<?php

	namespace App\Utilities;

	use App\Utilities\Blueprints\BaseFileUpload;
	use DateTimeInterface;

	/**
	 * Class Storage
	 *
	 * Provides a fluent interface for interacting with the filesystem.
	 *
	 * @package App\Utilities
	 */
	final class Storage
	{
		private static ?array $disk = null;

		/**
		 * Configure the default storage path.
		 *
		 * @param string $defaultPath
		 * @return void
		 */
		public static function configure(string $defaultPath = 'storage'): void
		{
			BaseFileUpload::setStoragePath($defaultPath);
		}

		/**
		 * Get a new instance of the storage handler.
		 *
		 * @param string $disk
		 * @return BaseFileUpload
		 */
		public static function disk(string $disk = 'local'): BaseFileUpload
		{
			if (!isset(self::$disk[$disk])) {
				self::$disk[$disk] = new BaseFileUpload($disk);
			}

			return self::$disk[$disk];
		}

		/**
		 * Store a file at the specified path.
		 *
		 * @param string $path
		 * @param string $contents
		 * @param string $disk
		 * @return bool
		 */
		public static function put(string $path, string $contents, string $disk = 'local'): bool
		{
			return self::disk($disk)->put($path, $contents);
		}

		/**
		 * Retrieve the contents of a file.
		 *
		 * @param string $path
		 * @param string $disk
		 * @return string|null
		 */
		public static function get(string $path, string $disk = 'local'): ?string
		{
			return self::disk($disk)->get($path);
		}

		/**
		 * Check if a file exists.
		 *
		 * @param string $path
		 * @param string $disk
		 * @return bool
		 */
		public static function exists(string $path, string $disk = 'local'): bool
		{
			return self::disk($disk)->exists($path);
		}

		/**
		 * Delete a file.
		 *
		 * @param string $path
		 * @param string $disk
		 * @return bool
		 */
		public static function delete(string $path, string $disk = 'local'): bool
		{
			return self::disk($disk)->delete($path);
		}

		/**
		 * Copy a file to a new location.
		 *
		 * @param string $from
		 * @param string $to
		 * @param string $disk
		 * @return bool
		 */
		public static function copy(string $from, string $to, string $disk = 'local'): bool
		{
			return self::disk($disk)->copy($from, $to);
		}

		/**
		 * Move a file to a new location.
		 *
		 * @param string $from
		 * @param string $to
		 * @param string $disk
		 * @return bool
		 */
		public static function move(string $from, string $to, string $disk = 'local'): bool
		{
			return self::disk($disk)->move($from, $to);
		}

		/**
		 * Get the file size in bytes.
		 *
		 * @param string $path
		 * @param string $disk
		 * @return int
		 */
		public static function size(string $path, string $disk = 'local'): int
		{
			return self::disk($disk)->size($path);
		}

		/**
		 * Get the last modified time of a file.
		 *
		 * @param string $path
		 * @param string $disk
		 * @return int
		 */
		public static function lastModified(string $path, string $disk = 'local'): int
		{
			return self::disk($disk)->lastModified($path);
		}

		/**
		 * Get the public URL for a file.
		 *
		 * @param string $path
		 * @param string $disk
		 * @return string
		 */
		public static function url(string $path, string $disk = 'local'): string
		{
			return self::disk($disk)->url($path);
		}

		/**
		 * Get a temporary URL for a file with an expiration date.
		 *
		 * @param string $path
		 * @param DateTimeInterface $expiration
		 * @param string $disk
		 * @return string
		 */
		public static function temporaryUrl(string $path, DateTimeInterface $expiration, string $disk = 'local'): string
		{
			return self::disk($disk)->temporaryUrl($path, $expiration);
		}

		/**
		 * Get all files in a directory.
		 *
		 * @param string $directory
		 * @param string $disk
		 * @return array<int, string>
		 */
		public static function allFiles(string $directory, string $disk = 'local'): array
		{
			return self::disk($disk)->allFiles($directory);
		}

		/**
		 * Get all directories within a directory.
		 *
		 * @param string $directory
		 * @param string $disk
		 * @return array<int, string>
		 */
		public static function allDirectories(string $directory, string $disk = 'local'): array
		{
			return self::disk($disk)->allDirectories($directory);
		}

		/**
		 * Create a new directory.
		 *
		 * @param string $directory
		 * @param string $disk
		 * @return bool
		 */
		public static function makeDirectory(string $directory, string $disk = 'local'): bool
		{
			return self::disk($disk)->makeDirectory($directory);
		}

		/**
		 * Delete a directory.
		 *
		 * @param string $directory
		 * @param string $disk
		 * @return bool
		 */
		public static function deleteDirectory(string $directory, string $disk = 'local'): bool
		{
			return self::disk($disk)->deleteDirectory($directory);
		}
	}
