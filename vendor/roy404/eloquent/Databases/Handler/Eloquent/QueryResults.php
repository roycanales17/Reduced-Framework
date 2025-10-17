<?php

	namespace App\Databases\Handler\Eloquent;

	use App\Databases\Database;
	use App\Databases\Facade\Connections;
	use App\Databases\Handler\Blueprints\QueryReturnType;
	use App\Databases\Handler\Collection;
	use App\Databases\Handler\Results;

	/**
	 * Abstract base class that implements the Results interface and provides
	 * common query execution and result transformation logic for Eloquent-style builders.
	 *
	 * It delegates execution to the Connections facade and wraps results into a
	 * Collection for convenient data access (row, field, col, etc).
	 */
	abstract class QueryResults implements Results
	{
		/**
		 * Executes the query based on the specified return type.
		 *
		 * @param QueryReturnType $returnType The type of result to return (ALL, COUNT, LAST_INSERT_ID, ROW_COUNT).
		 * @return mixed The raw execution result before being wrapped in a Collection.
		 */
		private function perform(QueryReturnType $returnType): mixed
		{
			$sql = !empty($this->query) ? $this->query : $this->rawSQL(false);
			return Connections::execute($this->server, $sql, $this->bindings, $returnType);
		}

		/**
		 * Get the first row of the result set as an associative array.
		 *
		 * @return array
		 */
		public function row(): array
		{
			$data = $this->perform(QueryReturnType::ALL);
			$obj = new Collection($data);
			return $obj->row();
		}

		/**
		 * Get the value of the first field in the first row of the result set.
		 *
		 * @return mixed
		 */
		public function field(): mixed
		{
			$data = $this->perform(QueryReturnType::ALL);
			$obj = new Collection($data);
			return $obj->field();
		}

		/**
		 * Get the values of the first column across all rows.
		 *
		 * @return array
		 */
		public function col(): array
		{
			$data = $this->perform(QueryReturnType::ALL);
			$obj = new Collection($data);
			return $obj->col();
		}

		/**
		 * Get the first row from the result set.
		 *
		 * @return mixed
		 */
		public function first(): mixed
		{
			$data = $this->perform(QueryReturnType::ALL);
			$obj = new Collection($data);
			return $obj->first();
		}

		/**
		 * Get the last row from the result set.
		 *
		 * @return mixed
		 */
		public function last(): mixed
		{
			$data = $this->perform(QueryReturnType::ALL);
			$obj = new Collection($data);
			return $obj->last();
		}

		/**
		 * Count the number of rows in the result set.
		 *
		 * @return int
		 */
		public function count(): int
		{
			$data = $this->perform(QueryReturnType::COUNT);
			$obj = new Collection(['count' => $data]);
			return $obj->count();
		}

		/**
		 * Fetch the full result set as an array.
		 *
		 * @return array
		 */
		public function fetch(): array
		{
			$data = $this->perform(QueryReturnType::ALL);
			$obj = new Collection($data);
			return $obj->fetch();
		}

		/**
		 * Extract values for a single column from the result set.
		 *
		 * @param string $column The column name to pluck.
		 * @return array
		 */
		public function pluck(string $column): array
		{
			$data = $this->perform(QueryReturnType::ALL);
			$obj = new Collection($data);
			return $obj->pluck($column);
		}

		/**
		 * Get the ID of the last inserted row.
		 *
		 * @return int
		 */
		public function lastInsertedID(): int
		{
			$data = $this->perform(QueryReturnType::LAST_INSERT_ID);
			$obj = new Collection(['last_insert_id' => $data]);
			return $obj->field();
		}

		/**
		 * Get the number of rows affected by the last write operation (INSERT/UPDATE/DELETE).
		 *
		 * @return int
		 */
		public function totalAffected(): int
		{
			$data = $this->perform(QueryReturnType::ROW_COUNT);
			$obj = new Collection($data);
			return $obj->field();
		}
	}