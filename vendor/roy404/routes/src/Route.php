<?php

	namespace App\Routes;

	use Closure;
	use Exception;
	use App\Routes\Scheme\Facade;
	use App\Routes\Scheme\Buffer;
	use App\Routes\Requests\Builder\{Delete, Get, Patch, Post, Put};
	use App\Routes\Configurations\Builder\{Controller, Group, Middleware, Prefix, Name, Domain, Resources, Where};

	/**
	 * Class Route
	 *
	 * This class extends the Facade and provides static methods for route registration and configuration.
	 * It allows defining routes for different HTTP methods (GET, POST, PUT, PATCH, DELETE) as well as
	 * additional configuration options like controllers, middleware, and prefixes.
	 *
	 * @method static Put put(string $uri, string|array|Closure $action = []) Defines a PUT route.
	 * @method static Patch patch(string $uri, string|array|Closure $action = []) Defines a PATCH route.
	 * @method static Delete delete(string $uri, string|array|Closure $action = []) Defines a DELETE route.
	 * @method static Get get(string $uri, string|array|Closure $action = []) Defines a GET route.
	 * @method static Post post(string $uri, string|array|Closure $action = []) Defines a POST route.
	 * @method static Group group(array $attributes, Closure $action) Registers a group of routes with shared configurations and middleware, enhancing route organization and reusability.
	 * @method static Controller controller(string $className) Registers a controller.
	 * @method static Middleware middleware(string|array $action) Registers middleware for the route.
	 * @method static Prefix prefix(string $prefix) Adds a prefix to the route URI.
	 * @method static Name name(string $name) Sets the name for the routes.
	 * @method static Domain domain(string|array $domain) Sets the domain for the routes.
	 * @method static Where where(string $key, string $expression) Registers a parameter pattern for the route.
	 */
	final class Route extends Facade
	{
		/**
		 * Configures the routing system with a root directory and a list of route definitions.
		 *
		 * Optionally, a prefix and domain can be applied globally to all routes in this configuration.
		 *
		 * @param string $root The base path or namespace for the route definitions.
		 * @param array $routes The array of route definitions to register.
		 * @param string $prefix Optional URI prefix to prepend to all routes.
		 * @param string $domain Optional domain to associate with the routes.
		 * @return static        Returns an instance of the configured Route facade.
		 * @throws Exception
		 */
		public static function configure(string $root, array $routes, string $prefix = '', string $domain = ''): self
		{
			return new Route(
				routes: $routes,
				root: $root,
				prefix: $prefix,
				reset: true,
				domain: $domain
			);
		}

		/**
		 * Generates a URI for a named route with optional parameter replacements.
		 *
		 * Replaces placeholders (e.g., `{id}`) in the route URI with values from $params.
		 * If the named route is not found, returns `'/'` as fallback.
		 *
		 * @param string $name           The name of the route.
		 * @param array<string, mixed> $params Associative array of parameters to substitute in the URI.
		 * @return string                The generated URI or `'/'` if the route was not found.
		 */
		public static function link(string $name, array $params = []): string
		{
			$routes = Buffer::fetch('routes') ?? [];

			foreach ($routes as $route) {
				if ($route['name'] === $name) {
					$uri = $route['uri'];

					foreach ($params as $key => $value) {
						$uri = str_replace("{{$key}}", $value, $uri);
					}

					return $uri;
				}
			}

			return '/';
		}
	}