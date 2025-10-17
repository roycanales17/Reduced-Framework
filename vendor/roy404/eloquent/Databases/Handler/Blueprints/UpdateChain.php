<?php

	namespace App\Databases\Handler\Blueprints;

	use App\Databases\Eloquent;
	use App\Databases\Handler\Eloquent\Builder;
	use Closure;

	class UpdateChain
	{
		private Eloquent $eloquent;

		public function __construct(string $table, array $data, ?Eloquent $eloquent = null) {
			$this->eloquent = is_null($eloquent) ? new Eloquent() : $eloquent;
			$this->eloquent->table($table);

			foreach ($data as $key => $value) {
				$this->eloquent->set($key, $value);
			}
		}

		public function where(string|Closure $col, mixed $OperatorOrValue = null, mixed $value = Builder::EMPTY): self {
			$this->eloquent->where($col, $OperatorOrValue, $value);
			return $this;
		}

		public function orWhere(string|Closure $col, mixed $OperatorOrValue = '', mixed $value = Builder::EMPTY): self {
			$this->eloquent->orWhere($col, $OperatorOrValue, $value);
			return $this;
		}

		public function execute(): int {
			return $this->eloquent->update();
		}
	}