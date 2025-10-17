<?php


	namespace App\Routes\Configurations\Blueprints;

	use App\Routes\Scheme\Buffer;
	use App\Routes\Scheme\Pal;

	trait Middleware
	{
		private array $middleware = [];

		protected function RegisterMiddleware(string|array $middleware): self
		{
			if (is_string($middleware)) {
				if (str_contains($middleware, '::') || str_contains($middleware, '@')) {

					if (str_contains($middleware, '@')) {
						$middleware = explode('@', $middleware);
						$middleware[] = 'method';

					} else {

						$middleware = explode('::', $middleware);
						$middleware[] = 'static';
					}

					$class = $middleware[0];
					$method = $middleware[1];

					if (!method_exists($class, $method))
						$middleware = [];

				} else {

					if (function_exists($middleware)) {
						$middleware = ['procedural', $middleware, 'function'];
						goto _register;
					}

					$controller = method_exists($this, 'GetControllerName') ? $this->GetControllerName() : '';
					if ($controller) {
						if (method_exists($controller, $middleware)) {
							if (Pal::checkIfMethodIsStatic($controller, $middleware)) {
								$middleware = [$controller, $middleware, 'static'];
							} else {
								$middleware = [$controller, $middleware, 'method'];
							}
						} else {
							$middleware = [];
						}
					} else {
						$controller = Buffer::fetch('controller');
						if ($controller && $controller = end($controller)) {
							if (method_exists($controller, $middleware)) {
								if (Pal::checkIfMethodIsStatic($controller, $middleware)) {
									$middleware = [$controller, $middleware, 'static'];
								} else {
									$middleware = [$controller, $middleware, 'method'];
								}
							} else {
								$middleware = [];
							}
						} else {
							$middleware = [];
						}
					}
				}
			} else {
				if (count($middleware) == 2) {
					$class = $middleware[0];
					$method = $middleware[1];
					if (method_exists($class, $method)) {
						if (Pal::checkIfMethodIsStatic($class, $method)) {
							$middleware = [$class, $method, 'static'];
						} else {
							$middleware = [$class, $method, 'method'];
						}
					} else {
						$middleware = [];
					}
				} else {
					$middleware = [];
				}
			}

			_register:

			if ($middleware)
				$this->middleware[] = $middleware;

			return $this;
		}

		protected function DestroyMiddleware(): void
		{
			$middlewares = $this->GetMiddlewares();
			for ($i = 0; $i < count($middlewares); $i++) {
				$middlewares_r = Buffer::fetch('middleware');

				if ($middlewares_r) {
					array_pop($middlewares_r);
					Buffer::replace('middleware', $middlewares_r);
				}
			}
		}

		protected function SetupMiddleware(): void
		{
			$middlewares = $this->GetMiddlewares();
			foreach ($middlewares as $middleware) {
				if (is_string($middleware)) {
					$controllers = Buffer::fetch('controller');

					if ($controllers) {
						$controller = end($controllers);
						if (method_exists($controller, $middleware)) {
							if (Pal::checkIfMethodIsStatic($controller, $middleware)) {
								$temp_middleware = [$controller, $middleware, 'static'];
							} else {
								$temp_middleware = [$controller, $middleware, 'method'];
							}
							$middleware = $temp_middleware;
						} else {
							$middleware = [];
						}
					} else {
						$middleware = [];
					}
				}

				if ($middleware && in_array(strtolower(Pal::baseClassName(get_called_class())), Pal::getRoutes('configurations'))) {
					Buffer::register('middleware', $middleware);
				}
			}
		}

		protected function GetMiddlewares(): array
		{
			return $this->middleware;
		}
	}