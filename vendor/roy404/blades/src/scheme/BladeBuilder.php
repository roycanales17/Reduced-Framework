<?php

	namespace App\View\Compilers\Scheme;

	use Closure;

	interface BladeBuilder
	{
		public function callback(Closure $callback): void;

		public function build(): string;
	}