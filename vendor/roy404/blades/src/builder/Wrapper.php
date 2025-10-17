<?php

	namespace App\View\Compilers\Builder;

	use App\View\Compilers\scheme\BladeBuilder;
	use App\View\Compilers\scheme\CompilerException;
	use Closure;

	final class Wrapper implements BladeBuilder
	{
		private string $content;
		private string $prefix = '';
		private string $suffix = '';
		private Closure|null $template = null;
		private array $protectedRanges = [];

		/**
		 * Content is the main component.
		 *
		 * @param string $content
		 */
		function __construct(string $content) {
			$this->content = $content;
		}

		/**
		 * Set the prefix.
		 *
		 * @param string $prefix
		 * @return void
		 * @throws CompilerException
		 */
		public function prefix(string $prefix): void  {
			if (!$prefix)
				throw CompilerException::invalidWrapper('prefix');

			$this->prefix = $prefix;
		}

		/**
		 * Set the suffix.
		 *
		 * @param string $suffix
		 * @return void
		 * @throws CompilerException
		 */
		public function suffix(string $suffix): void  {
			if (!$suffix)
				throw CompilerException::invalidWrapper('suffix');

			$this->suffix = $suffix;
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
		 */
		public function build(): string {
			if (!$this->content)
				return '';

			$offset = 0;
			$pattern = '/' . preg_quote($this->prefix, '/') . '(.*?)' . preg_quote($this->suffix, '/') . '/s';
			$this->protectedRanges = [];

			$this->content = preg_replace_callback($pattern, function ($matches) use (&$offset) {
				$fullMatch = $matches[0];
				$expression = $matches[1];

				$start = strpos($this->content, $fullMatch, $offset);
				if ($start === false) {
					return $fullMatch;
				}

				$end = $start + strlen($fullMatch);
				$offset = $end;

				if ($this->isInsideProtectedRange($start, $end)) {
					return $fullMatch;
				}

				$this->protectedRanges[] = [$start, $end];
				$callback = $this->template;

				return $callback($expression);
			}, $this->content);

			return $this->content;
		}

		private function isInsideProtectedRange(int $start, int $end): bool {
			foreach ($this->protectedRanges as [$rangeStart, $rangeEnd]) {
				if (
					($start >= $rangeStart && $start < $rangeEnd) ||
					($end > $rangeStart && $end <= $rangeEnd) ||
					($start <= $rangeStart && $end >= $rangeEnd)
				) {
					return true;
				}
			}
			return false;
		}
	}