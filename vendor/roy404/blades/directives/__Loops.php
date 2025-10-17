<?php

	use App\View\Compilers\Blade;
	use App\View\Compilers\templates\Loops;

	Blade::build(new Loops)->register(function (Blade $blade)
	{
		// Break
		$blade->directive('break', function () {
			return '<?php break; ?>';
		});

		// Continue
		$blade->directive('continue', function () {
			return '<?php continue; ?>';
		});

		// For loop
		$blade->directive('for', function ($expression) {
			return "<?php for($expression): ?>";
		});

		$blade->directive('endfor', function () {
			return "<?php endfor; ?>";
		});

		// Foreach loop
		$blade->directive('foreach', function ($expression) {
			return "<?php foreach($expression): ?>";
		});

		$blade->directive('endforeach', function () {
			return "<?php endforeach; ?>";
		});

		// While loop
		$blade->directive('while', function ($expression) {
			return "<?php while($expression): ?>";
		});

		$blade->directive('endwhile', function () {
			return "<?php endwhile; ?>";
		});

		// Do-while loop
		$blade->directive('do', function () {
			return "<?php do { ?>";
		});

		$blade->directive('enddo', function ($expression) {
			return "<?php } while($expression); ?>";
		});
	});