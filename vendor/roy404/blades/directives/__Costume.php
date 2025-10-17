<?php

	use App\View\Compilers\Blade;
	use App\View\Compilers\templates\Costume;

	Blade::build(new Costume)->register(function (Blade $blade)
	{
		$blade->directive('csrf', function () {
			$token = '';
			if (function_exists('csrf_token')) {
				$token = csrf_token();
			}
			return "<input type='hidden' name='csrf-token' value='$token'>";
		});

		$blade->directive('json', function ($expression) {
			return "<?= json_encode($expression) ?>";
		});

		$blade->directive('extends', function ($expression) use ($blade) {
			static $recentPath = [];

			$expression = trim($expression);
			if ($expression === '')
				return '';

			$template = preg_replace('/^["\']|["\']$/', '', trim($expression ,' '));
			$basePath = $blade->getProjectRootPath('/views/');

			if (!is_dir($basePath))
				$basePath = $blade->getProjectRootPath();

			$fullPath = $basePath . $template;

			$candidatePaths = [];
			if (pathinfo($fullPath, PATHINFO_EXTENSION)) {
				$candidatePaths[] = $fullPath;
			} else {
				$candidatePaths[] = $fullPath . '.blade.php';
				$candidatePaths[] = $fullPath . '.php';
				$candidatePaths[] = $fullPath . '.html';
			}

			foreach ($candidatePaths as $path) {
				if (file_exists($path)) {
					$recentPath[] = $path;
					return $blade->render(file_get_contents($path));
				}
			}

			$resolvedPath = '';
			if ($recentPath) {
				$resolvedPath = $recentPath[count($recentPath) - 1];
			}

			$blade->resolveError(debug_backtrace(), [
				'expression' => $expression,
				'candidatePaths' => $candidatePaths,
				'resolvedPath' => $resolvedPath,
				'template' => 'extends'
			]);
		});
	});