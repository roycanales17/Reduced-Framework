<?php

	namespace App\View\Compilers\Builder;

	use App\View\Compilers\scheme\BladeBuilder;
	use App\View\Compilers\scheme\CompilerException;
	use ReflectionException;
	use ReflectionFunction;
	use Closure;

	final class Directive implements BladeBuilder
	{
		private string $content;
		private string $directive = '';
		private array $expressionCache = [];
		private Closure|null $template = null;

		/**
		 * Content is the main component.
		 *
		 * @param string $content
		 */
		function __construct(string $content) {
			$this->content = $content;
		}

		/**
		 * Sets the directive
		 *
		 * @throws CompilerException
		 */
		public function directive(string $directive): void {
			if (!$directive)
				throw new CompilerException("Directive '{$directive}' not defined");

			$this->directive = $directive;
		}

		/**
		 * Store the template for replacement with expression
		 *
		 * @param Closure $callback
		 * @return void
		 */
		public function callback(Closure $callback): void {
			$this->template = $callback;
		}

		/**
		 * Starts to compile tags directives.
		 *
		 * @return string
		 * @throws ReflectionException
		 */
		public function build(): string {
			if (!$this->content)
				return '';

			$callback = $this->template;
			$isRequiredParams = $this->totalRequireExpressions($this->template);
			$pattern = '/@' . preg_quote($this->directive, '/') . '\s*\(/';

			if ($isRequiredParams) {
				if (preg_match_all($pattern, $this->content, $matches, PREG_OFFSET_CAPTURE)) {

					$length = strlen($this->content);
					foreach (array_reverse($matches[0]) as $match) {
						$start = $match[1] + strlen($match[0]);
						$depth = 1;
						$expression = '';

						for ($i = $start; $i < $length; $i++) {
							$char = $this->content[$i];

							if ($char === '(') {
								$depth++;
							} elseif ($char === ')') {
								$depth--;
								if ($depth === 0) {
									break;
								}
							}

							$expression .= $char;
						}

						$defaultParams = $this->defaultParams($isRequiredParams, $expression);
						$replaceStr = $callback(...$defaultParams);

						$replaceStart = $match[1];
						$replaceLength = strlen($match[0]) + strlen($expression) + 1;

						$this->content = substr_replace($this->content, $replaceStr, $replaceStart, $replaceLength);
						$length = strlen($this->content);
					}
				}
			} else {
				$pattern = '/@' . preg_quote($this->directive, '/') . '/';
				$this->content = preg_replace_callback($pattern, function ($matches) use ($callback) {
					return $callback();
				}, $this->content);
			}

			return $this->content;
		}

		/**
		 * @throws ReflectionException
		 */
		private function totalRequireExpressions(Closure $closure): int {
			if (isset($this->expressionCache[spl_object_hash($closure)])) {
				return $this->expressionCache[spl_object_hash($closure)];
			}

			$reflection = new ReflectionFunction($closure);
			$count = $reflection->getNumberOfParameters();
			$this->expressionCache[spl_object_hash($closure)] = $count;

			return $count;
		}

		private function defaultParams(int $total, string $expression): array {
			$return = [];
			$params = [
				'expression' => $expression,
				'content' => $this->content
			];

			$index = 1;
			foreach ($params as $key => $value) {
				$return[$key] = $value;
				if ($index == $total)
					break;
				$index++;
			}

			return $return;
		}
	}