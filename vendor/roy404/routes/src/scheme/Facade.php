<?php

	namespace App\Routes\Scheme;

	use App\Routes\Route;
	use Closure;
	use Exception;

	abstract class Facade
	{
		use Properties;

		/**
		 * Constructor for initializing the route handling class.
		 *
		 * @param string $method The HTTP method (e.g., GET, POST, PUT) associated with the route.
		 * @param array $params The parameters for the route.
		 * @param array $routes An array of routes to be registered.
		 * @param string $root The root path for the routes.
		 * @param string $prefix Global prefix for all the routes.
		 * @param string $domain Global domain checker for all the routes.
		 * @throws Exception
		 */
		function __construct(string $method = '', array $params = [], array $routes = [], string $root = '', array $response = [], string $prefix = '', bool $reset = false, string $domain = '')
		{
			$this->setMethod($method);
			$this->setParams($params);

			if ($reset)
				$this->refresh();

			if ($prefix)
				$this->setGlobalPrefix($prefix);

			if ($domain)
				$this->setGlobalDomain($domain);

			if ($routes)
				$this->setRoutes($routes);

			if ($root)
				$this->setRoot($root);

			if ($routes) {
				$this->loadRoutes(true);
				$this->loadRoutes(false);
			}

			if ($response) {
				self::setStaticResolved(true);
				self::setStaticContent($response['content'] ?? '');
				self::setStaticResponseType($response['type'] ?? '');
			}
		}

		/**
		 * Dynamically handles static method calls for route registration.
		 *
		 * @param string $name The HTTP method name (e.g., 'get', 'post', 'put', etc.).
		 * @param array $arguments The arguments passed, typically including the URI and action.
		 *
		 * @return object|null An instance of the registered route or null if no instance is found.
		 * @throws Exception
		 */
		public static function __callStatic(string $name, array $arguments):? object
		{
			return self::registerRoute([
				'method' => $name,
				'args' => $arguments
			]);
		}

		/**
		 * Register the new instance of route.
		 *
		 * @param array $args
		 * @return object|null
		 * @throws Exception
		 */
		protected static function registerRoute(array $args):? object
		{
			return Pal::performPrivateMethod(new static($args['method'], $args['args']), 'commence');
		}

		/**
		 * Executes the appropriate route logic based on the request method.
		 *
		 * This method determines whether the current HTTP method matches any of the registered routes
		 * in either the "requests" or "configurations" categories. If a match is found, it delegates
		 * the routing logic to the `performRoute` method.
		 *
		 * @return object|null Returns an object representing the route execution, or null if no route is matched.
		 */
		private function commence(): ?object
		{
			foreach ($this->getProtocols() as $protocol) {
				if (in_array($this->getMethod(), Pal::getRoutes($protocol))) {
					return $this->performRoute($this->getMethod(), $protocol);
				}
			}

			return null;
		}

		/**
		 * Loads and includes route files defined in the routes array,
		 * only if the current request URI starts with the defined global prefix (if any).
		 *
		 * Performs the following:
		 * - Checks for a global prefix using Pal::getGlobalPrefix()
		 * - Ensures the current request URI begins with that prefix (if set)
		 * - Includes route files if matched
		 * - Returns a 404 JSON response if no route is resolved
		 *
		 * @throws Exception If a route file does not exist.
		 */
		private function loadRoutes(bool $initialize): void
		{
			Pal::toggleInitializing($initialize);

			$matched = true;
			if ($globalDomain = Pal::getGlobalDomain()) {
				$requestDomain = $_SERVER['HTTP_HOST'] ?? '';
				$normalize = function ($domain) {
					$domain = strtolower($domain);
					$domain = preg_replace('#^www\.#', '', $domain);
					return explode(':', $domain)[0]; // Remove port
				};

				$isLocalhost = in_array($normalize($requestDomain), ['localhost', '127.0.0.1']);
				if (!$isLocalhost && $normalize($requestDomain) !== $normalize($globalDomain)) {
					$matched = false;
				}
			}

			if ($matched && ($globalPrefix = Pal::getGlobalPrefix())) {
				$requestUri = $_SERVER['REQUEST_URI'] ?? '';

				if (!(strpos($requestUri, '/' . $globalPrefix) === 0)) {
					$matched = false;
				}
			}

			if ($matched || Pal::isInitializing()) {
				foreach ($this->getRoutes() as $route) {
					$path = $this->buildPath($route);
					if (file_exists($path)) {
						require($path);
					} else {
						throw new Exception("[Route] File not exist: $path");
					}
				}
			}

			if (!$this->isResolved()) {
				http_response_code(404);
				new Route(response: [
					'content' => json_encode(['message' => '404 Page']),
					'type' => 'application/json'
				]);
			}
		}

		/**
		 * Returns all the routes detected.
		 *
		 * @param Closure $closure
		 * @return $this
		 */
		public function routes(Closure $closure): self
		{
			$closure(Buffer::fetch('routes') ?? []);
			return $this;
		}

		/**
		 * Executes a closure with the current content and response code.
		 *
		 * @param Closure $closure The closure to be executed.
		 */
		public function captured(Closure $closure, bool $exit = false): void
		{
			if (http_response_code() === 404) {
				return;
			}

			if ($this->isResolved()) {
				$closure($this->getContent(), http_response_code(), $this->getResponseType());

				if ($exit) exit();
			}
		}

		/**
		 * Handles the execution of a route by creating an instance of the corresponding route builder.
		 *
		 * This method dynamically creates an instance of a route builder class based on the method
		 * and type (e.g., "requests" or "configurations"), passing the URI and action as parameters.
		 *
		 * @param string $method The HTTP method (e.g., GET, POST, etc.).
		 * @param string $type The type of route configuration (e.g., "requests" or "configurations").
		 *
		 * @return object The instance of the route builder responsible for handling the route.
		 */
		private function performRoute(string $method, string $type): object
		{
			return Pal::createInstance("App\\Routes\\$type\\Builder\\$method", $this->params[0] ?? '', $this->params[1] ?? []);
		}
	}
