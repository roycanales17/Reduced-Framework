<?php

	namespace App\Utilities\Handler;

	use App\Bootstrap\Exceptions\StreamException;
	use App\Utilities\Stream;
	use App\View\Compilers\Blade;
	use App\View\Compilers\scheme\CompilerException;

	final class StreamHandler
	{
		private array $action = [];
		private array $extract;
		private string|null $class;
		private bool $asynchronous;

		/**
		 * Class constructor.
		 *
		 * Initializes the stream with a given action and optional constructor parameters.
		 *
		 * @param string|null $class Class to invoke
		 * @param array $constructParams Parameters to pass to the class constructor (if needed).
		 * @param bool $asynchronous Whether the stream should run asynchronously.
		 * @throws StreamException
		 */
		function __construct(null|string $class = null, array $constructParams = [], bool $asynchronous = false) {
			if ($class !== null && !is_subclass_of($class, Component::class)) {
				throw new StreamException("Class {$class} must extend " . Component::class);
			}

			$this->class = $class;
			$this->asynchronous = $asynchronous;
			$this->extract = $constructParams;
		}

		/**
		 * On stream echo we also allow to execute more function
		 *
		 * @throws StreamException
		 */
		public function with(array|string $action, ...$args): self {
			if ($this->class && $action) {
				if (is_string($action)) {
					$class = $this->class;
					$method = $action;

					if (!method_exists($class, $method)) {
						throw new StreamException("Method {$method} does not exist.");
					}

					$this->action = [$class, $method, $args];
				} else {
					$class = $action[0] ?? null;
					$method = $action[1] ?? null;

					if (!$class || !$method) {
						throw new StreamException("Both class and method must be provided.");
					}

					if (!class_exists($class)) {
						throw new StreamException("Class {$class} does not exist.");
					}

					if (!is_subclass_of($class, Component::class)) {
						throw new StreamException("Component '".get_class($class)."' does not implement " . Component::class);
					}

					if (!method_exists($class, $method)) {
						throw new StreamException("Method {$method} does not exist.");
					}

					$action[2] = $args;
					$this->action = $action;
				}
			}

			return $this;
		}

		/**
		 * This performs the other component request.
		 * NOTE: The returned string should be output raw in Blade with {!! !!} to avoid HTML escaping.
		 *
		 * Example usage:
		 * <button wire:click='{!! target([MyClass::class, "method"], $arg) !!}'></button>
		 *
		 * @param array $action [className, methodName]
		 * @param mixed ...$argv Method arguments
		 * @return string generated wire attributes
		 * @throws StreamException
		 */
		public function target(array $action, ...$argv): string
		{
			$class = $action[0] ?? null;
			$method = $action[1] ?? null;
			$isTarget = $action[2] ?? true;

			if (!class_exists($class)) {
				throw new StreamException('Class not found: ' . $class);
			}

			if (!is_string($method) || !method_exists($class, $method)) {
				throw new \InvalidArgumentException("Invalid target action method '{$method}'.");
			}

			$encodedArgs = [];
			foreach ($argv as $arg) {
				if (is_array($arg)) {
					$json = json_encode($arg);
					$json = str_replace('"', "'", $json);
					$jsonArg = trim($json, '"');
				} else {
					$jsonArg = json_encode($arg, JSON_UNESCAPED_SLASHES);
					if (is_string($arg)) {
						$jsonArg = "'" . trim($jsonArg, '"') . "'";
					}
				}
				$encodedArgs[] = $jsonArg;
			}
			$argsString = implode(', ', $encodedArgs);

			/** @var Component $class */
			if ($isTarget) {
				if (method_exists($class, 'identifier')) {
					$identifier = $class::identifier();
					$wireTarget = 'wire:target="' . $identifier;
				} else {
					throw new StreamException("`identifier` method is required for target action '" . json_encode($action) . "'.");
				}
			}

			return $method . '(' . $argsString . ')" ' . ($wireTarget ?? '');
		}

		/**
		 * This performs the component function request itself.
		 * NOTE: The returned string should be output raw in Blade with {!! !!} to avoid HTML escaping.
		 *
		 * Example usage:
		 * <button wire:click='{!! execute([MyClass::class, "method"], $arg) !!}'></button>
		 *
		 * @param array $action [className, methodName]
		 * @param mixed ...$argv Method arguments
		 * @return string generated wire attributes
		 * @throws StreamException
		 */
		public function execute(array $action, ...$argv): string
		{
			$action[2] = false;
			return $this->target($action, ...$argv);
		}

		/**
		 * This returns the component interface.
		 *
		 * @throws CompilerException
		 * @throws StreamException
		 */
		public function __toString(): string {
			ob_start();

			$class = $this->class;
			if (empty($class) && $this->action)
				$class = $this->action[0] ?? null;

			if ($class) {
				$component = new $class();

				if (!stream::verifyComponent($component))
					return ob_get_clean();

				$component->initialize($class, $this->extract);
				if ($this->action) {
					$actionMethod = $this->action[1];
					$actionArgs = $this->action[2] ?? [];

					$component->$actionMethod(...$actionArgs);
				}

				if ($this->asynchronous) {
					echo($component->parse(preloader: true));
				} else {
					echo($component->parse());
				}

				return Blade::compile(ob_get_clean());
			}

			throw new StreamException("Unable to generate stream, class is not provided.");
		}
	}