<?php

	namespace App\Routes\Scheme;

	use Closure;
	use ReflectionException;
	use ReflectionFunction;
	use ReflectionMethod;
	use InvalidArgumentException;
	use ReflectionClass;

	/**
	 * Trait Reflections
	 *
	 * This trait provides functionality to perform actions based on class methods, functions, or closures,
	 * resolving parameters and invoking them as needed. It uses reflection to dynamically inspect and call
	 * methods or functions with the appropriate parameters.
	 */
	trait Reflections
	{
		/**
		 * Perform an action using a callable, which can be a method, function, or closure.
		 *
		 * @param string|array|Closure $action The callable to invoke, can be a method array in the format [ClassName, 'methodName'],
		 *                                       a function name as a string, or a closure.
		 * @param array $params
		 * @return mixed The result of the invoked callable.
		 * @throws ReflectionException If the action is invalid or if required constructor parameters are missing.
		 */
		protected function performAction(string|array|Closure $action, array $params = []): mixed
		{
			$paramsValue = [];
			$reflection = $this->getReflection($action);

			foreach ($reflection->getParameters() as $param) {

				$type = $param->getType();
				$typeName = $type?->getName();
				$key = $param->getName();
				$value = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;

				if ($typeName && class_exists($typeName)) {
					$paramsValue[] = new $typeName();

				} elseif ($params && in_array($key, array_keys($params))) {
					$paramsValue[] = $params[$key];
				} else {
					if (!$value) {
						$paramsValue[] = match ($typeName ?? 'default') {
							'int', 'float' => 0,
							'string'       => '',
							'bool'         => false,
							'array'        => [],
							default        => null,
						};
					} else {
						$paramsValue[] = $value;
					}
				}
			}

			if ($reflection instanceof ReflectionFunction) {
				return $reflection->invokeArgs($paramsValue);

			} elseif ($reflection instanceof ReflectionMethod) {

				$classReflection = new ReflectionClass($reflection->class);
				$constructor = $classReflection->getConstructor();

				if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
					throw new \InvalidArgumentException($reflection->getDeclaringClass()->getName() . '::' . $reflection->getName() . " requires construct params.");
				}

				$instance = $classReflection->newInstance();
				return $reflection->invokeArgs($instance, $paramsValue);
			}

			throw new InvalidArgumentException("Invalid action provided [2]");
		}

		/**
		 * Get reflection information for the provided callable (function or method).
		 *
		 * @param string|array|Closure $functionOrClosureOrClassMethod The callable to inspect.
		 * @return ReflectionMethod|ReflectionFunction The reflection object for the callable.
		 * @throws InvalidArgumentException|ReflectionException If the provided callable is invalid.
		 */
		private function getReflection(string|array|Closure $functionOrClosureOrClassMethod): ReflectionMethod|ReflectionFunction
		{
			if (is_string($functionOrClosureOrClassMethod) && function_exists($functionOrClosureOrClassMethod)) {
				return new ReflectionFunction($functionOrClosureOrClassMethod);
			}

			if ($functionOrClosureOrClassMethod instanceof Closure) {
				return new ReflectionFunction($functionOrClosureOrClassMethod);
			}

			if (is_array($functionOrClosureOrClassMethod) && count($functionOrClosureOrClassMethod) >= 2) {
				$class = $functionOrClosureOrClassMethod[0];
				$method = $functionOrClosureOrClassMethod[1];

				if (method_exists($class, $method)) {
					return new ReflectionMethod($class, $method);
				}
			}

			throw new InvalidArgumentException("Invalid action provided [1]");
		}
	}
