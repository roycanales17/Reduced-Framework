<?php

	namespace App\Utilities;

	use App\Utilities\Blueprints\BaseCarbon;

	/**
	 * Utility wrapper around BaseCarbon with helper methods.
	 */
	final class Carbon
	{
		/**
		 * Get the current date and time.
		 *
		 * @return BaseCarbon
		 */
		public static function now(): BaseCarbon
		{
			return BaseCarbon::now();
		}

		/**
		 * Get today's date as a formatted string.
		 *
		 * @return string
		 */
		public static function today(): string
		{
			return BaseCarbon::now()->toDateString();
		}

		/**
		 * Add days to the current date.
		 *
		 * @param int $days
		 * @return BaseCarbon
		 */
		public static function addDays(int $days): BaseCarbon
		{
			return BaseCarbon::now()->addDays($days);
		}

		/**
		 * Subtract days from the current date.
		 *
		 * @param int $days
		 * @return BaseCarbon
		 */
		public static function subtractDays(int $days): BaseCarbon
		{
			return BaseCarbon::now()->subDays($days);
		}

		/**
		 * Get the current date and time in a custom format.
		 *
		 * @param string $format
		 * @return string
		 */
		public static function format(string $format = 'Y-m-d H:i:s'): string
		{
			return BaseCarbon::now()->format($format);
		}

		/**
		 * Parse a date string into a BaseCarbon instance.
		 *
		 * @param string $date
		 * @return BaseCarbon
		 */
		public static function parse(string $date): BaseCarbon
		{
			return BaseCarbon::parse($date);
		}

		/**
		 * Get the difference in days between two dates.
		 *
		 * @param string $date1
		 * @param string $date2
		 * @return int
		 */
		public static function diffInDays(string $date1, string $date2): int
		{
			$carbonDate1 = BaseCarbon::parse($date1);
			$carbonDate2 = BaseCarbon::parse($date2);

			return $carbonDate1->diffInDays($carbonDate2);
		}

		/**
		 * Check if a date is in the future.
		 *
		 * @param string $date
		 * @return bool
		 */
		public static function isFuture(string $date): bool
		{
			return BaseCarbon::parse($date)->isFuture();
		}

		/**
		 * Check if a date is in the past.
		 *
		 * @param string $date
		 * @return bool
		 */
		public static function isPast(string $date): bool
		{
			return BaseCarbon::parse($date)->isPast();
		}
	}
