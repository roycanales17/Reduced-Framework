<?php

	namespace App\Routes\Configurations\Blueprints;

	use App\Routes\Scheme\Buffer;
	use App\Routes\Scheme\Pal;

	trait Name
	{
		private string $name = '';

		protected function RegisterName(string $routeName): self
		{
			$routeName = strtolower($routeName);
			if ($routeName) {
				$this->name = $routeName;
			}

			return $this;
		}

		protected function DestroyName(): void
		{
			if ($this->getRouteName()) {
				$names = Buffer::fetch('names');
				if ($names) {
					array_pop($names);
					Buffer::replace('names', $names);
				}
			}
		}

		protected function SetupName(): void
		{
			$prefix = strtolower($this->getRouteName());
			if ($prefix && in_array(strtolower(Pal::baseClassName(get_called_class())), Pal::getRoutes('configurations'))) {
				Buffer::register('names', $prefix);
			}
		}

		protected function getRouteName(): string
		{
			return $this->name;
		}
	}