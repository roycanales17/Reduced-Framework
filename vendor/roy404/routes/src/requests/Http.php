<?php

	namespace App\Routes\Requests;

	use Closure;
	use ReflectionException;

	use App\Routes\Route;
	use App\Routes\Scheme\Buffer;
	use App\Routes\Scheme\Pal;
	use App\Routes\Scheme\Protocol;
	use App\Routes\Scheme\Reflections;
	use App\Routes\Scheme\Validations;

	abstract class Http
	{
		use Protocol;
		use Validations;
		use Reflections;

		public function __construct(string $uri, mixed $actions)
		{
			$this->registerAction($actions);
			$this->registerURI($uri);
		}

		private function setupDomain(): void
		{
			$globalDomain = Buffer::fetch('domain') ?? [];
			$domains = method_exists($this, 'getDomain') ? $this->getDomain() : [];
			$allowedDomains = array_merge($globalDomain, $domains);

			if ($allowedDomains) {
				$this->registerDomainName($allowedDomains);
			}
		}

		private function setupRouteMiddleware(): void
		{
			$middlewares = method_exists($this, 'GetMiddlewares') ? $this->GetMiddlewares() : [];
			if ($globalMiddlewares = Buffer::fetch('middleware'))
				$middlewares = array_merge($globalMiddlewares, $middlewares);

			if ($middlewares) {
				$this->registerMiddlewares($middlewares);
			}
		}

		private function setupRouteParamsExpression(): void
		{
			$expressions = method_exists($this, 'getWhereExpression') ? $this->getWhereExpression() : [];
			$globalExpressions = Buffer::fetch('where') ?? [];

			$activeExpressions = array_merge($globalExpressions, $expressions);
			if ($activeExpressions) {
				$this->registerExpressions($activeExpressions);
			}
		}

		private function setupRouteAction(): void
		{
			if (is_string($this->getActions())) {
				$controller = method_exists($this, 'GetControllerName') ? $this->GetControllerName() : '';
				if ($controller) {
					$this->registerAction([$controller, $this->getActions()]);
				} else {
					if ($controllers = Buffer::fetch('controller')) {
						if ($controller = end($controllers)) {
							$this->registerAction([$controller, $this->getActions()]);
						}
					}
				}
			}
		}

		private function setupRouteName(string|null &$name = null): void
		{
			$name = '';
			$routeNames = Buffer::fetch('names') ?? [];
			$routeName = method_exists($this, 'getRouteName') ? $this->getRouteName() : '';

			if ($routeNames) {
				$name .= implode('.', $routeNames);
			}

			if ($routeName) {
				$name .= ( $routeNames ? '.' : '' ) . $routeName;
			}
		}

		private function capture(Closure|string $closure, int $code = 200, string $type = ''): void
		{
			ob_start();
			http_response_code($code);
			is_string($closure) ? print($closure) : $closure();

			if (!$type) {
				$headers = getallheaders();
				if (isset($headers['Content-Type'])) {
					$contentType = $headers['Content-Type'];
					$type = $contentType;
				} else {
					$type = 'text/html';
				}
			}

			new Route(response: [
				'content' => ob_get_clean(),
				'type' => $type,
			]);
			$this->toggleStatus(true);
		}

		/**
		 * @throws ReflectionException
		 */
		public function __destruct()
		{
			$this->setupDomain();
			$this->setupRouteName($routeName);
			$this->setupRouteAction();
			$this->setupRouteMiddleware();
			$this->setupRouteParamsExpression();
			$this->registerRoutes($prefixes = $this->getActivePrefix(), $routeName);

			if (!Pal::isInitializing() && !$this->getRouteStatus() && $this->validateURI($this->getURI(), $prefixes, $params)) {

				if (!$this->validateDomain($this->getRequestDomain()))
					return;

				if (!$this->validateMethodRequest(Pal::baseClassName(get_called_class())))
					return;

				if (!$this->validateParamsExpressions($this->getExpressions(), $params)) {
					$this->capture(json_encode(['message' => 'Bad Request']), 400, 'application/json');
					return;
				}

				if (!$this->validateMiddleware($this->fetchMiddlewares())) {
					$this->capture(json_encode(['message' => 'Unauthorized']), 401, 'application/json');
					return;
				}

				$this->capture( function () use ($params) {
					$captured = $this->performAction($this->getActions(), $params);

					if (is_array($captured)) {
						echo(json_encode($captured, JSON_PRETTY_PRINT));
					} else {
						echo($captured);
					}
				});
			}
		}
	}