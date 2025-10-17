<?php

	namespace App\Routes\Configurations\Builder;

	use App\Routes\Configurations\Blueprints\Controller;
	use App\Routes\Configurations\Blueprints\Domain;
	use App\Routes\Configurations\Blueprints\Group;
	use App\Routes\Configurations\Blueprints\Middleware;
	use App\Routes\Configurations\Blueprints\Name;
	use App\Routes\Configurations\Blueprints\Where;
	use App\Routes\Configurations\Blueprints\Prefix as BasePrefix;
	use App\Routes\Configurations\Config;

	class Prefix extends Config
	{
		private string $prefixProperty;

		use BasePrefix {
			RegisterPrefix as private prefix;
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
		use Controller {
			RegisterController as public controller;
		}
		use Middleware {
			RegisterMiddleware as public middleware;
		}

		function __construct(string $prefix)
		{
			$this->prefixProperty = $prefix;
		}

		protected function register(): void
		{
			$this->RegisterPrefix($this->prefixProperty);
		}
	}