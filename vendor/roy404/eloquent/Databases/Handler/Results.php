<?php

	namespace App\Databases\Handler;

	/**
	 * Interface Results
	 *
	 * Defines the standard contract for handling database query results.
	 * Implementations should provide convenient methods for accessing rows,
	 * fields, and collections of data.
	 *
	 * @package Databases\Handler
	 */
	interface Results
	{
		/**
		 * Get the first row as an associative array.
		 *
		 * @return array
		 */
		public function row(): array;

		/**
		 * Get the first field (the first column of the first row).
		 *
		 * @return mixed
		 */
		public function field(): mixed;

		/**
		 * Get the values of the first column across all rows.
		 *
		 * @return array
		 */
		public function col(): array;

		/**
		 * Get the first row of the result set.
		 *
		 * @return mixed
		 */
		public function first(): mixed;

		/**
		 * Get the last row of the result set.
		 *
		 * @return mixed
		 */
		public function last(): mixed;

		/**
		 * Get the count of rows in the result set.
		 *
		 * @return int
		 */
		public function count(): int;

		/**
		 * Fetch all results as an array of rows.
		 *
		 * @return array
		 */
		public function fetch(): array;

		/**
		 * Extract the values of a given column from all rows.
		 *
		 * @param string $column The column name.
		 * @return array
		 */
		public function pluck(string $column): array;
	}