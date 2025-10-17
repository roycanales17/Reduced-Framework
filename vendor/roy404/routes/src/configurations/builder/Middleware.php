<?php

	namespace App\Routes\Configurations\Builder;

	use App\Routes\Configurations\Blueprints\Domain;
	use App\Routes\Configurations\Blueprints\Middleware as BaseMiddleware;
	use App\Routes\Configurations\Blueprints\Controller;
	use App\Routes\Configurations\Blueprints\Group;
	use App\Routes\Configurations\Blueprints\Name;
	use App\Routes\Configurations\Blueprints\Where;
	use App\Routes\Configurations\Blueprints\Prefix;
	use App\Routes\Configurations\Config;

	class Middleware extends Config
	{
		private string|array $middlewareProperty;

		use BaseMiddleware {
			RegisterMiddleware as private middleware;
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
		use Controller {
			RegisterController as public controller;
		}

		function __construct(string|array $middleware)
		{
			$this->middlewareProperty = $middleware;
		}

		protected function register(): void
		{
			$this->RegisterMiddleware($this->middlewareProperty);
		}
	}