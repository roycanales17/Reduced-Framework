<?php

	namespace App\Routes\Scheme;

	use Closure;
	use Exception;

	trait Protocol
	{
		private string $uri = '';
		private array $domainNames = [];
		private array $middlewares = [];
		private array $expressions = [];
		private string|array|Closure $actions = [];
		private static bool $status = false;

		protected function registerURI(string $uri): void
		{
			$this->uri = $uri;
		}

		protected function registerAction(string|array|Closure $action): void
		{
			if (is_array($action)) {
				$class = $action[0] ?? null;
				$method = $action[1] ?? null;

				if (!class_exists($class)) {
					throw new Exception("Class {$class} does not exist");
				}

				if (!method_exists($class, $method)) {
					throw new Exception("Class {$class} does not have method {$method}");
				}
			}

			$this->actions = $action;
		}

		protected function registerDomainName(array $domains): void
		{
			$this->domainNames = $domains;
		}

		protected function registerMiddlewares(array $middleware): void
		{
			$this->middlewares = $middleware;
		}

		protected function registerExpressions(array $expressions): void
		{
			$this->expressions = $expressions;
		}

		protected function toggleStatus(bool $found): void
		{
			self::$status = $found;
		}

		protected function getExpressions(): array
		{
			return $this->expressions;
		}

		protected function getRouteStatus(): bool
		{
			return self::$status;
		}

		protected function getURI(): string
		{
			return $this->uri;
		}

		protected function getDomainName(): array
		{
			return $this->domainNames;
		}

		protected function getActions(): string|array|Closure
		{
			return $this->actions;
		}

		protected function fetchMiddlewares(): array
		{
			return $this->middlewares;
		}

		protected function getRequestDomain(): string
		{
			return $_SERVER['HTTP_HOST'] ?? 'localhost';
		}

		private function getActivePrefix(): array
		{
			$globalPrefix = Buffer::fetch('prefix') ?? [];
			$prefix = method_exists($this, 'getPrefix') ? $this->getPrefix() : [];

			return array_merge($globalPrefix, $prefix);
		}

		protected function registerRoutes($prefixes, $routeName): void
		{
			$action = $this->getActions();
			if (is_object($action)) {
				$action = 'Closure';
			}

			$prefix = '';
			$globalPrefix = Pal::getGlobalPrefix();

			if ($globalPrefix)
				$prefix .= "/". $globalPrefix;

			if ($prefixes)
				$prefix .= '/'. trim(implode('/', $prefixes), '/');

			Buffer::register('routes', [
				'uri' => $prefix . '/' . ltrim($this->getURI(), '/'),
				'name' => $routeName,
				'actions' => $action,
				'middlewares' => $this->fetchMiddlewares(),
				'expressions' => $this->getExpressions(),
				'domains' => $this->getDomainName()
			]);
		}
	}