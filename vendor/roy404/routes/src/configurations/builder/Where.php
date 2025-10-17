<?php

	namespace App\Routes\Configurations\Builder;

	use App\Routes\Configurations\Blueprints\Where as BaseWhere;
	use App\Routes\Configurations\Blueprints\Domain;
	use App\Routes\Configurations\Blueprints\Name;
	use App\Routes\Configurations\Blueprints\Controller;
	use App\Routes\Configurations\Blueprints\Group;
	use App\Routes\Configurations\Blueprints\Middleware;
	use App\Routes\Configurations\Blueprints\Prefix;
	use App\Routes\Configurations\Config;

	class Where extends Config
	{
		private string $whereKeyProperty;
		private string $whereValueProperty;

		use BaseWhere {
			RegisterWhere as private where;
		}
		use Name {
			RegisterName as public name;
		}
		use Domain {
			RegisterDomain as public domain;
		}
		use Middleware {
			RegisterMiddleware as public middleware;
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

		function __construct(string $key, string $expression)
		{
			$this->whereKeyProperty = $key;
			$this->whereValueProperty = $expression;
		}

		protected function register(): void
		{
			$this->RegisterWhere($this->whereKeyProperty, $this->whereValueProperty);
		}
	}