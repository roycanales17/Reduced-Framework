<?php

	namespace App\Routes\Scheme;

	use ReflectionException;

	/**
	 * Trait RouteValidation
	 *
	 * This trait provides methods for validating routing constraints, middleware, and URIs.
	 * It is designed to be used in routing contexts where constraints and middleware checks are required
	 * to ensure that incoming requests match the expected patterns and rules defined for the routes.
	 */
	trait Validations
	{
		use Reflections;

		/**
		 * Assign middleware to the route.
		 *
		 * This method allows you to attach one or multiple middleware to the route. Middleware can be provided
		 * as a string (e.g., 'auth') or as an array. If a string middleware contains a colon, it is split into
		 * an array to handle parameters (e.g., 'auth:admin'). If the middleware is not a global function, the
		 * method will try to find it as a method within the currently fetched controller, allowing for controller-based
		 * middleware assignment.
		 *
		 * @param array $middlewares The middleware to be attached to the route. It can be a single middleware
		 *                                 as a string or an array of middleware.
		 * @return bool
		 * @throws ReflectionException
		 */
		protected function validateMiddleware(array $middlewares): bool
		{
			foreach ($middlewares as $middleware) {
				$class = $middleware[0];
				$method = $middleware[1];
				$type = $middleware[2];

				if ($class === 'procedural') {
					return $this->performAction($method);
				} else {
					$instance = ($type === 'method') ? Pal::createInstance($class) : $class;
					if (!($type === 'method' ? $instance?->$method() ?? false : $instance::$method())) {
						return false;
					}
				}
			}

			return true;
		}

		/**
		 * Validate if the provided domain is allowed.
		 *
		 * This method checks if a given domain is part of the allowed domains for the application.
		 * Allowed domains can be retrieved dynamically by calling a `getDomainName` method if it exists.
		 * The comparison is case-insensitive to ensure consistent matching.
		 *
		 * @param string $domain The domain to validate.
		 * @return bool True if the domain is allowed, false otherwise.
		 */
		protected function validateDomain(string $domain): bool
		{
			if ($domain) {
				$allowedDomains = method_exists($this, 'getDomainName') ? $this->getDomainName() : [];
				if ($allowedDomains && !in_array(strtolower($domain), array_map('strtolower', $allowedDomains))) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Validate if the provided request method matches the expected method.
		 *
		 * This method checks if the current HTTP request method matches the specified method.
		 * It compares the methods in a case-insensitive manner to avoid mismatches.
		 *
		 * @param string $method The HTTP method (e.g., 'GET', 'POST') to validate.
		 * @return bool True if the request method matches, false otherwise.
		 */
		protected function validateMethodRequest(string $method): bool
		{
			$request = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
			if ($method) {
				return $request === strtoupper($method);
			}

			return false;
		}

		/**
		 * Validates parameters against a set of expressions.
		 *
		 * This method checks if the provided parameters match the specified patterns in the expressions array.
		 * Each expression consists of a key and a regex pattern. If a parameter corresponding to a key exists,
		 * its value is validated against the provided regex pattern.
		 *
		 * The validation fails if:
		 * - Any parameter value does not match its respective pattern.
		 * - The expressions array is non-empty, but the params array is empty.
		 *
		 * @param array $expressions An array of expressions, where each expression is an array with:
		 *                           - string $key: The parameter name to validate.
		 *                           - string $pattern: The regex pattern to validate the parameter value.
		 * @param array $params      An associative array of parameters to validate.
		 *                           The keys correspond to the parameter names and values are their respective data.
		 *
		 * @return bool True if all parameters match their respective patterns, false otherwise.
		 */
		protected function validateParamsExpressions(array $expressions, array $params): bool
		{
			if ($expressions) {

				if (empty($params))
					return false;

				foreach ($expressions as $expression) {

					if (!is_array($expression) || count($expression) < 2)
						continue;

					$key = strtolower($expression[0]);
					$pattern = $expression[1];

					if (isset($params[$key])) {
						$value = $params[$key];

						if (!preg_match("/$pattern/", $value)) {
							return false;
						}
					}
				}
			}

			return true;
		}

		/**
		 * Validate the provided URI against the current request URI.
		 *
		 * This method checks if the requested URI matches the defined route URI, accounting for dynamic segments.
		 *
		 * @param string $uri The route URI to validate against the request URI.
		 * @param array $prefix Optional prefixes to prepend to the URI for validation.
		 * @return bool Returns true if the request URI matches the defined route URI, otherwise false.
		 */
		protected function validateURI(string $uri, array $prefix = [], array|null &$params = []): bool
		{
			$globalPrefix = Pal::getGlobalPrefix();

			if ($globalPrefix)
				array_unshift($prefix, $globalPrefix);

			$matched = 0;
			$url = $_SERVER['REQUEST_URI'] ?? '';
			$uri = $this->URISlashes($uri, $prefix);
			$route_uri = $this->separateSubDirectories($uri);
			$route_url = $this->separateSubDirectories($url);

			if (count($route_uri) === count($route_url)) {
				foreach ($route_uri as $index => $directory) {
					if (isset($route_url[$index])) {
						if (preg_match('/^\{[^{}]+\}$/', $directory)) {
							$params[str_replace(['{', '}'], '', $directory)] = preg_replace('/\?.*/', '', $route_url[$index]);
							$matched++;
						} else {
							if (strtolower($directory) === strtolower(strstr($route_url[$index], '?', true) ?: $route_url[$index])) {
								$matched++;
							}
						}
					}
				}
			} else {
				$matched = -1;
			}

			if (is_null($params))
				$params = [];

			return ($matched === count($route_uri));
		}

		/**
		 * Ensure the URI has the correct leading and trailing slashes.
		 *
		 * This method normalizes the URI by ensuring it starts and ends with a slash,
		 * and appends the base prefix if necessary.
		 *
		 * @param string|null $uri The URI to normalize.
		 * @param array $prefixes Optional prefixes to prepend to the URI.
		 * @return string Returns the normalized URI.
		 */
		private function URISlashes(?string $uri, array $prefixes = []): string
		{
			if (empty($uri)) {
				return '';
			}

			$prefixPath = $prefixes ? '/' . implode('/', $prefixes) : '';
			return $prefixPath . '/' . trim($uri, '/');
		}

		/**
		 * Separate a string into subdirectories.
		 *
		 * This method splits a URI string into its constituent segments, filtering out any empty segments.
		 *
		 * @param string|null $value The URI string to separate.
		 * @return array Returns an array of non-empty segments.
		 */
		private function separateSubDirectories(?string $value): array
		{
			return array_values(array_filter(explode('/', strtok($value, '?')), function ($value) {
				return $value !== "";
			}));
		}
	}
