<?php

	namespace App\Routes\Configurations\Builder;

	use App\Routes\Configurations\Blueprints\Domain as BaseDomain;
	use App\Routes\Configurations\Blueprints\Controller;
	use App\Routes\Configurations\Blueprints\Group;
	use App\Routes\Configurations\Blueprints\Middleware;
	use App\Routes\Configurations\Blueprints\Name;
	use App\Routes\Configurations\Blueprints\Where;
	use App\Routes\Configurations\Blueprints\Prefix;
	use App\Routes\Configurations\Config;

	class Domain extends Config
	{
		private string|array $domainProperty;

		use BaseDomain {
			RegisterDomain as private domain;
		}
		use Where {
			RegisterWhere as public where;
		}
		use Controller {
			RegisterController as public controller;
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

		function __construct(string|array $domain)
		{
			$this->domainProperty = $domain;
		}

		protected function register(): void
		{
			$this->RegisterDomain($this->domainProperty);
		}
	}