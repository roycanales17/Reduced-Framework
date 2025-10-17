<?php

	namespace App\View\Compilers\Scheme;

	use Closure;

	interface ViewsInterface
	{
		public function directive(string $directive, Closure $callback): void;

		public function wrap(string $prefix, string $suffix, Closure $callback): void;

		public function compile(string $content): string;
	}