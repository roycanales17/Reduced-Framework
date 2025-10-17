<?php

	namespace App\Console;

	use Closure;
	use Commands\Lists;
	use ReflectionClass;

	/**
	 * Class Terminal
	 *
	 * Provides a lightweight command-line interface similar to Laravel's Artisan.
	 * Handles command registration, execution, styled console output,
	 * interactive input, and question prompts.
	 *
	 * Features:
	 * - Auto-discovery of command classes in a given namespace.
	 * - Styled console output (info, error, success, warning).
	 * - Command history with navigation (arrow keys).
	 * - Interactive prompts for user input.
	 * - Configurable color codes and icons.
	 */
	class Terminal
	{
		/** @var string Success icon */
		private string $success = '✅';

		/** @var string Error icon */
		private string $error = '❌';

		/** @var string Loader/spinner icon */
		private string $loader = '⏳';

		/** @var array Registered commands */
		private static array $commands = [];

		/** @var bool Whether the terminal has been configured */
		private static bool $configured = false;

		/** @var array Command history logs */
		private static array $logs = [];

		// ANSI color codes
		public const RED     = 31;
		public const GREEN   = 32;
		public const YELLOW  = 33;
		public const BLUE    = 34;
		public const MAGENTA = 35;
		public const CYAN    = 36;
		public const GRAY    = 37;

		/**
		 * Configure the terminal by loading commands from a namespace/directory.
		 *
		 * @param array|string $paths One or more directories to scan for command classes.
		 * @param string       $root  Root path for resolving command directories.
		 * @return void
		 */
		public static function config(array|string $paths, string $root = ''): void
		{
			if (!$root) {
				$root = dirname(__DIR__);
			}

			if (is_string($paths)) {
				$paths = [$paths];
			}

			foreach ($paths as $namespace) {
				$namespace = trim($namespace, '/');
				$directory = $root . DIRECTORY_SEPARATOR . $namespace;

				if (is_dir($directory)) {
					foreach (scandir($directory) as $file) {
						if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
							require_once "$directory/$file";
							$class = $namespace . '\\' . pathinfo($file, PATHINFO_FILENAME);

							if (class_exists($class)) {
								$reflection = new ReflectionClass($class);
								if ($reflection->isSubclassOf(Command::class) && !$reflection->isAbstract()) {
									$obj = new $class();
									self::$commands[] = [
										'object'      => $obj,
										'signature'   => $obj->getSignature(),
										'description' => $obj->getDescription()
									];
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Capture command-line input and execute the appropriate command.
		 *
		 * @param array $args  Command-line arguments (e.g. $argv).
		 * @param bool  $reset If true, forces interactive mode.
		 * @return void
		 */
		public static function capture(array $args, bool $reset = false): void
		{
			self::setupDefaultCommands();

			$command = $args[1] ?? '';
			$params  = array_slice($args, 2);

			echo "\n";

			if (!$reset && $command) {
				if (self::handle($command, $params)) {
					return;
				}
			} else {
				if ($reset) {
					if (self::handle($command, $params, true)) {
						self::input(function ($args) {
							self::capture($args, true);
						}, true);
						return;
					}
				} else {
					if (self::handle('list', $params, true)) {
						self::input(function ($args) {
							self::capture($args, true);
						}, true);
						return;
					}
				}
			}

			self::error('Invalid action.');

			if ($reset) {
				self::input(function ($args) {
					self::capture($args, true);
				}, true);
			}
		}

		/**
		 * Handle execution of a specific command.
		 *
		 * @param string $command  The command signature.
		 * @param array  $args     Arguments to pass to the command.
		 * @param bool   $execute  Whether to also run its `execute()` method if available.
		 * @return bool            True if the command was found and executed, false otherwise.
		 */
		public static function handle(string $command, array $args = [], bool $execute = false): bool
		{
			foreach (self::$commands as $attr) {
				$signature = $attr['signature'];
				$object    = $attr['object'];

				if ($signature === $command && method_exists($object, 'handle')) {
					call_user_func_array([$object, 'handle'], $args);
					if ($execute && method_exists($object, 'execute')) {
						$object->execute();
					}
					return true;
				}
			}

			return false;
		}

		/**
		 * Print an info message with optional ANSI color.
		 *
		 * @param string $message The message to display.
		 * @param int    $code    ANSI color code (default = 0).
		 * @param bool   $return  If true, returns the formatted string instead of printing.
		 * @return string
		 */
		public static function info(string $message, int $code = 0, bool $return = false): string
		{
			if ($code < 0 || $code > 97) {
				$code = 0;
			}

			$formatted = '';
			$lines     = explode("\n", $message);
			foreach ($lines as $line) {
				$formatted .= "\e[{$code}m{$line}\e[0m\n";
			}
			$formatted = rtrim($formatted, "\n");

			if ($return) {
				return $formatted;
			}

			echo "$formatted\n";
			return '';
		}

		/**
		 * Capture interactive input from the user with history and arrow key support.
		 *
		 * @param Closure $callback Function to call with parsed input.
		 * @param bool    $format   If true, parses the input into command/args format.
		 * @return void
		 */
		public static function input(Closure $callback, bool $format = false): void
		{
			$input         = '';
			$historyIndex  = null;
			$cursorPosition = 0;
			$logs          = self::$logs ?? [];

			system('stty -icanon -echo');

			while (true) {
				$char = fgetc(STDIN);

				if ($char === "\033") { // Escape sequence
					$char2 = fgetc(STDIN);
					$char3 = fgetc(STDIN);
					$seq   = $char . $char2 . $char3;

					switch ($seq) {
						case "\033[A": // Arrow Up
							if (!empty($logs)) {
								if ($historyIndex === null) {
									$historyIndex = count($logs) - 1;
								} elseif ($historyIndex > 0) {
									$historyIndex--;
								}

								echo "\r\033[K";
								$input          = $logs[$historyIndex];
								$cursorPosition = strlen($input);
								echo $input;
							}
							break;

						case "\033[B": // Arrow Down
							if (!empty($logs) && $historyIndex !== null) {
								if ($historyIndex < count($logs) - 1) {
									$historyIndex++;
									$input = $logs[$historyIndex];
								} else {
									$historyIndex = null;
									$input        = '';
								}

								echo "\r\033[K";
								$cursorPosition = strlen($input);
								echo $input;
							}
							break;

						case "\033[C": // Arrow Right
							if ($cursorPosition < strlen($input)) {
								echo "\033[1C";
								$cursorPosition++;
							}
							break;

						case "\033[D": // Arrow Left
							if ($cursorPosition > 0) {
								echo "\033[1D";
								$cursorPosition--;
							}
							break;

						default:
							echo "\nUnknown sequence: " . bin2hex($seq) . "\n";
					}
				} elseif ($char === "\n") { // Enter
					echo "\n\n";
					break;
				} elseif (ord($char) === 127) { // Backspace
					if (strlen($input) > 0 && $cursorPosition > 0) {
						$cursorPosition--;
						$input = substr($input, 0, -1);
						echo "\033[1D \033[1D";
					}
				} else { // Regular characters
					$input .= $char;
					$cursorPosition++;
					echo $char;
				}
			}

			system('stty icanon echo');

			if ($input !== '') {
				self::$logs[] = $input;
			}

			if ($format) {
				preg_match_all('/("[^"]*"|\'[^\']*\'|\S+)/', trim($input), $matches);
				$input = array_map(fn($v) => trim($v, '\'"'), $matches[0]);
				array_unshift($input, 'artisan');
			}

			if ($input) {
				$callback($input);
			}
		}

		/**
		 * Print an error message.
		 *
		 * @param string $message The error text.
		 * @param bool   $newLine Whether to append a newline.
		 * @return void
		 */
		public static function error(string $message, bool $newLine = true): void
		{
			$n = $newLine ? "\n" : "";
			self::info("[ERROR] $message$n", self::RED);
		}

		/**
		 * Print a success message.
		 *
		 * @param string $message The success text.
		 * @param bool   $newLine Whether to append a newline.
		 * @return void
		 */
		public static function success(string $message, bool $newLine = true): void
		{
			$n = $newLine ? "\n" : "";
			self::info("[SUCCESS] $message$n", self::GREEN);
		}

		/**
		 * Print a warning message.
		 *
		 * @param string $message The warning text.
		 * @param bool   $newLine Whether to append a newline.
		 * @return void
		 */
		public static function warn(string $message, bool $newLine = true): void
		{
			$n = $newLine ? "\n" : "";
			self::info("[WARNING] $message$n", self::YELLOW);
		}

		/**
		 * Fetch all registered commands with signature and description.
		 *
		 * @return array
		 */
		public static function fetchAllCommands(): array
		{
			$commands = [];
			foreach (self::$commands as $command) {
				$commands[] = [
					'signature'   => $command['signature'],
					'description' => $command['description']
				];
			}

			return $commands;
		}

		/**
		 * Ask a multiple-choice question in the console.
		 *
		 * @param string $message  The question to ask.
		 * @param array  $options  List of options (default: ['no', 'yes']).
		 * @return int             The index of the selected option.
		 */
		public static function question(string $message, array $options = ['no', 'yes']): int
		{
			echo $message . PHP_EOL . PHP_EOL;

			foreach ($options as $index => $option) {
				echo "  [$index] $option" . PHP_EOL;
			}

			echo PHP_EOL . "Select an option (0-" . (count($options) - 1) . "): ";

			while (true) {
				$input = trim(fgets(STDIN));

				if (is_numeric($input) && isset($options[(int)$input])) {
					return (int)$input;
				}

				echo "Invalid selection. Try again: ";
			}
		}

		/**
		 * Setup default commands by auto-loading them from the commands directory.
		 *
		 * @return void
		 */
		private static function setupDefaultCommands(): void
		{
			if (!self::$configured) {
				self::$configured = true;
				self::config('commands');

				foreach (Lists::retrieveLists() as $command) {
					self::$logs[] = $command;
				}
			}
		}
	}