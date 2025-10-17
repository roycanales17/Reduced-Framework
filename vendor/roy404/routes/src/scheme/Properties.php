<?php

	namespace App\Routes\Scheme;

	trait Properties
	{
		private array $params;
		private string $method;
		private array $protocol = [
			'requests',
			'configurations'
		];

		private static string $root = '';
		private static mixed $content = '';
		private static array $routes = [];
		private static bool $resolved = false;
		private static string $responseType = 'text/html';

		protected function setGlobalPrefix(string $prefix): void
		{
			Pal::registerGlobalPrefix($prefix);
		}

		protected function setGlobalDomain(string $domain): void
		{
			Pal::registerGlobalDomain($domain);
		}

		protected function setParams(array $params): void
		{
			$this->params = $params;
		}

		protected function setMethod(string $method): void
		{
			$this->method = $method;
		}

		protected function setRoutes(array $routes): void
		{
			self::$routes = $routes;
		}

		protected function setRoot(string $root): void
		{
			if (file_exists($root))
				self::$root = $root;
			else
				throw new \Exception("[Route] Root folder for routes does not exist: $root");
		}

		protected function getRoutes(): array
		{
			return self::$routes;
		}

		protected function getMethod(): string
		{
			return $this->method;
		}

		protected function getProtocols(): array
		{
			return $this->protocol;
		}

		protected function getRoot(): string
		{
			return self::$root;
		}

		protected function getContent(): string
		{
			return self::$content;
		}

		protected function getResponseType(): string
		{
			return self::$responseType;
		}

		protected function buildPath(string $path): string
		{
			return $this->getRoot() . "/" . preg_replace('/\.php$/', '', $path) . '.php';
		}

		protected function isResolved(): bool
		{
			return self::$resolved;
		}

		protected static function setStaticResolved(bool $opt): void
		{
			self::$resolved = $opt;
		}

		protected static function setStaticContent(string $content): void
		{
			self::$content = $content;
		}

		protected static function setStaticResponseType(string $type): void
		{
			self::$responseType = strtolower($type);
		}

		protected static function setStaticRoot(string $root): void
		{
			self::$root = $root;
		}

		protected function refresh(): void
		{
			self::$root = '';
			self::$content = '';
			self::$routes = [];
			self::$responseType = 'text/html';
		}
	}