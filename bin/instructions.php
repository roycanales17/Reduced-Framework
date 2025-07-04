<?php

$projectName = basename(getcwd());

echo PHP_EOL;
echo "\033[32mProject '$projectName' created! Next steps:\033[0m" . PHP_EOL;
echo "  1. cd $projectName" . PHP_EOL;
echo "  2. npm install" . PHP_EOL;
echo "  3. Run 'npm run watch' and 'php artisan serve' in separate terminal sessions" . PHP_EOL;
echo PHP_EOL;
echo "\033[32m✔️  All set! Happy coding! 🚀\033[0m";
echo PHP_EOL;

$binPath = __DIR__;
if (PHP_OS_FAMILY === 'Windows') {
	// Windows-compatible directory removal
	shell_exec("rmdir /S /Q \"$binPath\"");
} else {
	// Unix/macOS-compatible directory removal
	shell_exec("rm -rf \"$binPath\"");
}
