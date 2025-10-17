<?php

	namespace App\Databases;

	use Closure;
	use App\Databases\Handler\Eloquent\Builder;

	/**
	 * Class Eloquent
	 *
	 * A query builder for constructing SQL queries with a fluent API,
	 * supporting nested conditions, subqueries, ordering, limits, and offsets.
	 *
	 * @package Databases
	 */
	class Eloquent extends Builder
	{
		/**
		 * Create a new Eloquent query builder instance.
		 *
		 * @param string $server The database server identifier (default: "master").
		 */
		public function __construct(string $server = 'master') {
			$this->server = $server;
		}

		/**
		 * Set the table to query.
		 *
		 * @param string $table The table name.
		 * @return $this
		 */
		public function table(string $table): self {
			$this->table = $table;
			return $this;
		}

		/**
		 * Specify columns to select in the query.
		 *
		 * @param string|Closure ...$columns List of column names or closures for sub-selects.
		 * @return $this
		 */
		public function select(string|Closure ...$columns): self {
			$this->columns = $columns ?: ['*'];
			return $this;
		}

		/**
		 * Add a column/value pair for the UPDATE statement.
		 *
		 * Example:
		 * ```php
		 * $query->table('users')
		 *       ->set('name', 'John')
		 *       ->set('email', 'john@example.com');
		 * ```
		 *
		 * @param string $column
		 * @param mixed $value
		 * @return $this
		 */
		public function set(string $column, mixed $value): self
		{
			$this->sets[$column] = '?';
			$this->bindings[] = $value;

			return $this;
		}


		/**
		 * Add a WHERE condition to the query.
		 *
		 * Supports closures for nested conditions:
		 * ```php
		 * $query->where(function($q) {
		 *     $q->where('age', '>', 18)->orWhere('status', 'active');
		 * });
		 * ```
		 *
		 * @param string|Closure $col             Column name or closure for nested where.
		 * @param mixed          $OperatorOrValue Operator or value depending on usage.
		 * @param mixed          $value           Value (optional if operator omitted).
		 * @return $this
		 */
		public function where(string|Closure $col, mixed $OperatorOrValue = null, mixed $value = self::EMPTY): self {
			if ($col instanceof Closure) {
				$nested = new self();
				$col($nested);
				$this->wheres[] = ['nested', $nested->wheres, 'AND'];
				$this->bindings = array_merge($this->bindings, $nested->bindings);
				return $this;
			}

			if ($value === self::EMPTY) {
				$this->wheres[] = ["$col = ?", 'AND'];
				$this->bindings[] = $OperatorOrValue;
			} else {
				$this->wheres[] = ["$col $OperatorOrValue ?", 'AND'];
				$this->bindings[] = $value;
			}

			return $this;
		}

		/**
		 * Add an OR WHERE condition to the query.
		 *
		 * @param string|Closure $col             Column name or closure for nested where.
		 * @param mixed          $OperatorOrValue Operator or value depending on usage.
		 * @param mixed          $value           Value (optional if operator omitted).
		 * @return $this
		 */
		public function orWhere(string|Closure $col, mixed $OperatorOrValue = '', mixed $value = self::EMPTY): self {
			if ($col instanceof Closure) {
				$nested = new self();
				$col($nested);
				$this->wheres[] = ['nested', $nested->wheres, 'OR'];
				$this->bindings = array_merge($this->bindings, $nested->bindings);
				return $this;
			}

			if ($value === self::EMPTY) {
				$this->wheres[] = ["$col = ?", 'OR'];
				$this->bindings[] = $OperatorOrValue;
			} else {
				$this->wheres[] = ["$col $OperatorOrValue ?", 'OR'];
				$this->bindings[] = $value;
			}

			return $this;
		}

		/**
		 * Add a WHERE condition comparing two columns.
		 *
		 * @param string $first    The first column.
		 * @param string $operator The comparison operator (=, !=, <, >, etc.).
		 * @param string $second   The second column.
		 * @param string $boolean  Boolean operator (AND/OR).
		 * @return $this
		 */
		public function whereColumn(string $first, string $operator, string $second, string $boolean = 'AND'): self
		{
			$this->wheres[] = ["$first $operator $second", $boolean];
			return $this;
		}

		/**
		 * Add an OR WHERE column comparison condition.
		 *
		 * @param string $first    The first column.
		 * @param string $operator The comparison operator.
		 * @param string $second   The second column.
		 * @return $this
		 */
		public function orWhereColumn(string $first, string $operator, string $second): self
		{
			return $this->whereColumn($first, $operator, $second, 'OR');
		}

		/**
		 * Add a WHERE condition with a subquery.
		 *
		 * Example:
		 * ```php
		 * $query->whereSub('users.id', 'IN', function($q) {
		 *     $q->table('orders')->select('user_id')->where('status', 'active');
		 * });
		 * ```
		 *
		 * @param string  $column   The column to compare.
		 * @param string  $operator The operator (=, IN, etc.).
		 * @param Closure $callback The subquery callback.
		 * @param string  $boolean  Boolean operator (AND/OR).
		 * @return $this
		 */
		public function whereSub(string $column, string $operator, Closure $callback, string $boolean = 'AND'): self
		{
			$sub = new self();
			$callback($sub);

			$subSql = "({$sub->rawSQL(false)})";
			$this->wheres[] = ["$column $operator $subSql", $boolean];
			$this->bindings = array_merge($this->bindings, $sub->bindings);

			return $this;
		}

		/**
		 * Add an OR WHERE condition with a subquery.
		 *
		 * @param string  $column   The column to compare.
		 * @param string  $operator The operator (=, IN, etc.).
		 * @param Closure $callback The subquery callback.
		 * @return $this
		 */
		public function orWhereSub(string $column, string $operator, Closure $callback): self
		{
			return $this->whereSub($column, $operator, $callback, 'OR');
		}

		/**
		 * Add an ORDER BY clause to the query.
		 *
		 * @param string $column The column to sort by.
		 * @param string $sort   Sort direction (ASC/DESC).
		 * @return $this
		 */
		public function orderBy(string $column, string $sort = 'ASC'): self {
			$this->orders[] = "$column $sort";
			return $this;
		}

		/**
		 * Add a LIMIT clause to the query.
		 *
		 * @param int $limit The maximum number of rows.
		 * @return $this
		 */
		public function limit(int $limit): self {
			$this->limit = $limit;
			return $this;
		}

		/**
		 * Add an OFFSET clause to the query.
		 *
		 * @param int $offset Number of rows to skip.
		 * @return $this
		 */
		public function offset(int $offset): self {
			$this->offset = $offset;
			return $this;
		}
	}