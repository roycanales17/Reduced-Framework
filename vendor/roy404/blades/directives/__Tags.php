<?php

	use App\View\Compilers\Blade;
	use App\View\Compilers\templates\Tags;

	Blade::build(new Tags)->register(function (Blade $blade)
	{
		$blade->wrap("{{--", "--}}", function ($expression) {
			return "<?php /* $expression */ ?>";
		});

		$blade->wrap("{{", "}}", function ($expression) {
			return "<?= htmlentities($expression ?? '') ?>";
		});

		$blade->wrap("{!!", "!!}", function ($expression) {
			return "<?= $expression ?? '' ?>";
		});

		$blade->wrap('@php', '@endphp', function ($expression) {
			return "<?php $expression ?>";
		});
	});