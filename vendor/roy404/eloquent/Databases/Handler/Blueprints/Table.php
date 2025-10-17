<?php

	namespace App\Databases\Handler\Blueprints;

	class Table
	{
		protected string $table;
		protected array $columns = [];
		protected array $options = [];
		protected ?string $lastColumn = null;

		public function __construct(string $table)
		{
			$this->table = $table;
		}

		/**
		 * Define an auto-incrementing primary key column.
		 */
		public function id(string $name = 'id', int $startingIndex = 0, ?int $length = null): static
		{
			$column = "`{$name}` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY";

			if ($length) {
				$column = "`{$name}` INT({$length}) UNSIGNED AUTO_INCREMENT PRIMARY KEY";
			}

			$this->columns[$name] = $column;
			$this->lastColumn = $name;

			if ($startingIndex > 0) {
				$this->options['AUTO_INCREMENT'] = $startingIndex;
			}

			return $this;
		}

		/**
		 * Define a VARCHAR column.
		 */
		public function string(string $name, int $length = 255): static
		{
			$this->columns[$name] = "`{$name}` VARCHAR({$length})";
			$this->lastColumn = $name;
			return $this;
		}

		/**
		 * Define a TEXT column.
		 */
		public function text(string $name): static
		{
			$this->columns[$name] = "`{$name}` TEXT";
			$this->lastColumn = $name;
			return $this;
		}

		/**
		 * Define an INT column.
		 */
		public function integer(string $name): static
		{
			$this->columns[$name] = "`{$name}` INT";
			$this->lastColumn = $name;
			return $this;
		}

		/**
		 * Define a DECIMAL column.
		 */
		public function decimal(string $name, int $precision = 8, int $scale = 2): static
		{
			$this->columns[$name] = "`{$name}` DECIMAL({$precision},{$scale})";
			$this->lastColumn = $name;
			return $this;
		}

		/**
		 * Define a BOOLEAN column.
		 */
		public function boolean(string $name): static
		{
			$this->columns[$name] = "`{$name}` TINYINT(1)";
			$this->lastColumn = $name;
			return $this;
		}

		/**
		 * Define a TIMESTAMP column.
		 */
		public function timestamp(string $name): static
		{
			$this->columns[$name] = "`{$name}` TIMESTAMP";
			$this->lastColumn = $name;
			return $this;
		}

		/**
		 * Define created_at and updated_at timestamp columns.
		 */
		public function timestamps(): static
		{
			$this->columns['created_at'] = "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
			$this->columns['updated_at'] = "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
			return $this;
		}

		/**
		 * Add a UNIQUE index for the given column.
		 */
		public function unique(string $column): static
		{
			$this->columns["unique_{$column}"] = "UNIQUE (`{$column}`)";
			return $this;
		}

		/**
		 * Add a DEFAULT value to the last defined column.
		 */
		public function default(mixed $value): static
		{
			if ($this->lastColumn && isset($this->columns[$this->lastColumn])) {
				$formatted = is_numeric($value) ? $value : "'{$value}'";
				$this->columns[$this->lastColumn] .= " DEFAULT {$formatted}";
			}
			return $this;
		}

		/**
		 * Set DEFAULT CURRENT_TIMESTAMP for the last defined column.
		 */
		public function defaultNow(): static
		{
			if ($this->lastColumn && isset($this->columns[$this->lastColumn])) {
				$this->columns[$this->lastColumn] .= " DEFAULT CURRENT_TIMESTAMP";
			}
			return $this;
		}

		/**
		 * Set ON UPDATE CURRENT_TIMESTAMP for the last defined column.
		 */
		public function updateNow(): static
		{
			if ($this->lastColumn && isset($this->columns[$this->lastColumn])) {
				$this->columns[$this->lastColumn] .= " ON UPDATE CURRENT_TIMESTAMP";
			}
			return $this;
		}

		/**
		 * Compile the schema definition to SQL.
		 */
		public function toSql(string $type): string
		{
			if ($type === 'create') {
				$cols = implode(", ", $this->columns);
				$sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` ({$cols})";

				if (!empty($this->options)) {
					foreach ($this->options as $key => $val) {
						$sql .= " {$key}={$val}";
					}
				}

				return $sql . ";";
			}

			if ($type === 'alter') {
				$cols = implode(", ", array_map(fn($col) => "ADD {$col}", $this->columns));
				return "ALTER TABLE `{$this->table}` {$cols};";
			}

			return '';
		}
	}
