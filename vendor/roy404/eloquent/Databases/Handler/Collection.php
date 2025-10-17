<?php

	namespace App\Databases\Handler;

	/**
	 * Class Collection
	 *
	 * A lightweight result wrapper that provides convenience methods
	 * to access database query results (rows, columns, fields).
	 *
	 * Implements the Results interface.
	 *
	 * @package Databases\Handler
	 */
	class Collection implements Results
	{
		/**
		 * The query results as a normalized array.
		 *
		 * @var array
		 */
		private array $result;

		/**
		 * Create a new Collection instance.
		 *
		 * Automatically normalizes input into a 2D array of rows.
		 *
		 * @param array|int|string $data The raw result set or scalar value.
		 */
		public function __construct(array|int|string $data)
		{
			if (!is_array($data)) {
				$data = [['data' => $data]];
			}

			if ($this->isAssociative($data)) {
				$data = [$data];
			}

			$this->result = $data;
		}

		/**
		 * Get the first row of the result set (or empty array if none).
		 *
		 * @return array
		 */
		public function row(): array
		{
			return $this->first() ?? [];
		}

		/**
		 * Get the first field (value of the first column in the first row).
		 *
		 * @return mixed|null
		 */
		public function field(): mixed
		{
			$result = $this->first();
			if ($result !== null) {
				return reset($result);
			}

			return null;
		}

		/**
		 * Get the values of the first column as an array.
		 *
		 * @return array
		 */
		public function col(): array
		{
			$row = $this->first();
			if ($row !== null) {
				$column = key($row);
				return array_column($this->result, $column);
			}

			return [];
		}

		/**
		 * Get the first row in the result set.
		 *
		 * @return mixed|null
		 */
		public function first(): mixed
		{
			return $this->result[0] ?? null;
		}

		/**
		 * Get the last row in the result set.
		 *
		 * @return mixed|null
		 */
		public function last(): mixed
		{
			if (empty($this->result)) {
				return null;
			}

			return $this->result[count($this->result) - 1];
		}

		/**
		 * Count the number of results or return a COUNT(*) field.
		 *
		 * @param bool $countResults If true, counts the rows instead of returning COUNT(*) field.
		 * @return int
		 */
		public function count(bool $countResults = false): int
		{
			if ($countResults) {
				return count($this->result);
			}

			return intval( $this->field() );
		}

		/**
		 * Fetch all results as an array.
		 *
		 * @return array
		 */
		public function fetch(): array
		{
			return $this->result;
		}

		/**
		 * Extract values of a single column across all rows.
		 *
		 * @param string $column The column name.
		 * @return array
		 */
		public function pluck(string $column): array
		{
			return array_map(fn($item) => $item[$column] ?? null, $this->result);
		}

		/**
		 * Determine if the array is associative.
		 *
		 * @param array $arr
		 * @return bool
		 */
		private function isAssociative(array $arr): bool
		{
			if ([] === $arr) return false;
			return array_keys($arr) !== range(0, count($arr) - 1);
		}
	}