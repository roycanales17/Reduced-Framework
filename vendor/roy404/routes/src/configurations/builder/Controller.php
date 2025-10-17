<?php

	namespace App\Routes\Configurations\Builder;

	use App\Routes\Configurations\Config;
	use App\Routes\Configurations\Blueprints\Where;
	use App\Routes\Configurations\Blueprints\Group;
	use App\Routes\Configurations\Blueprints\Middleware;
	use App\Routes\Configurations\Blueprints\Prefix;
	use App\Routes\Configurations\Blueprints\Name;
	use App\Routes\Configurations\Blueprints\Domain;
	use App\Routes\Configurations\Blueprints\Controller as BaseController;

	class Controller extends Config
	{
		private string $classNameProperty;

		use BaseController {
			RegisterController as private controller;
		}
		use Where {
			RegisterWhere as public where;
		}
		use Domain {
			RegisterDomain as public domain;
		}
		use Name {
			RegisterName as public name;
		}
		use Group {
			RegisterGroup as public group;
		}
		use Prefix {
			RegisterPrefix as public prefix;
		}
		use Middleware {
			RegisterMiddleware as public middleware;
		}

		function __construct(string $className)
		{
			$this->classNameProperty = $className;
		}

		protected function register(): void
		{
			$this->RegisterController($this->classNameProperty);
		}
	}