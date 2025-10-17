<?php

	namespace App\Routes\Configurations;

	use App\Routes\Scheme\Pal;

	abstract class Config
	{
		private function PerformConfigurations(string $action): void
		{
			$traits = class_uses(static::class);
			foreach ($traits as $provider) {
				$className = Pal::baseClassName($provider);
				$method = "$action$className";

				if (method_exists($this, $method)) {
					$this->$method();
				}
			}
		}

		private function PerformGroups(): void
		{
			$groups = method_exists($this, 'getGroups') ? $this->getGroups() : [];;
			foreach ($groups as $group) {
				call_user_func($group);
			}
		}

		function __destruct()
		{
			$this->register();
			$this->PerformConfigurations('Setup');
			$this->PerformGroups();
			$this->PerformConfigurations('Destroy');
		}
	}