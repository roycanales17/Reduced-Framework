<?php

	namespace App\Routes\Configurations\Blueprints;

	use App\Routes\Scheme\Buffer;
	use App\Routes\Scheme\Pal;
	use Exception;

	trait Controller
	{
		private string $controller = '';

		protected function RegisterController(string $className): self
		{
			if (!class_exists($className))
				throw new Exception("Controller class $className does not exist.");

			$this->controller = $className;
			return $this;
		}

		protected function DestroyController(): void
		{
			if ($this->GetControllerName()) {
				$controllers = Buffer::fetch('controller');
				if ($controllers) {
					array_pop($controllers);
					Buffer::replace('controller', $controllers);
				}
			}
		}

		protected function SetupController(): void
		{
			$className = $this->GetControllerName();
			if ($className && in_array(strtolower(Pal::baseClassName(get_called_class())), Pal::getRoutes('configurations'))) {
				Buffer::register('controller', $className);
			}
		}

		protected function GetControllerName(): string
		{
			return $this->controller;
		}
	}