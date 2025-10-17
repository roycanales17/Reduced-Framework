<?php

	use App\View\Compilers\Blade;
	use App\View\Compilers\templates\Variables;

	Blade::build(new Variables)->register(function (Blade $blade)
	{
		$blade->directive('post', function ($expression) {
			$trimmed = trim($expression);

			if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
				return "<?= \$_POST$trimmed ?? '' ?>";

			return "<?= \$_POST[$trimmed] ?? '' ?>";
		});

		$blade->directive('get', function ($expression) {
			$trimmed = trim($expression);

			if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
				return "<?= \$_GET$trimmed ?? '' ?>";

			return "<?= \$_GET[$trimmed] ?? '' ?>";
		});

		$blade->directive('server', function ($expression) {
			$trimmed = trim($expression);

			if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
				return "<?= \$_SERVER$trimmed ?? '' ?>";

			return "<?= \$_SERVER[$trimmed] ?? '' ?>";
		});

		$blade->directive('session', function ($expression) {
			$trimmed = trim($expression);

			if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
				return "<?= \$_SESSION$trimmed ?? '' ?>";

			return "<?= \$_SESSION[$trimmed] ?? '' ?>";
		});
	});