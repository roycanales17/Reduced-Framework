<?php

	namespace App\Routes\Configurations\Blueprints;

	use App\Routes\Scheme\Buffer;
	use App\Routes\Scheme\Pal;

	trait Where
	{
		private array $where = [];

		protected function RegisterWhere(string $key, string $expression): self
		{
			$this->where[] = [$key, $expression];
			return $this;
		}

		protected function DestroyWhere(): void
		{
			if ($this->getWhereExpression()) {
				$where = Buffer::fetch('where');
				if ($where) {
					array_pop($where);
					Buffer::replace('where', $where);
				}
			}
		}

		protected function SetupWhere(): void
		{
			$wheres = $this->getWhereExpression();
			if ($wheres && in_array(strtolower(Pal::baseClassName(get_called_class())), Pal::getRoutes('configurations'))) {
				foreach ($wheres as $where) {
					Buffer::register('where', $where);
				}
			}
		}

		protected function getWhereExpression(): array
		{
			return $this->where;
		}
	}