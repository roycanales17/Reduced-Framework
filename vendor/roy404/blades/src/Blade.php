<?php

	namespace App\View\Compilers;

	use App\View\Compilers\scheme\CompilerException;
	use App\View\Compilers\scheme\ViewsInterface;
	use Exception;
	use Closure;
	use Error;

	/**
	 * The Blade class provides a lightweight template engine with support
	 * for registering custom directives and compiling template content.
	 */
	final class Blade
	{
		/**
		 * Holds the compiler implementation.
		 *
		 * @var ViewsInterface
		 */
		private ViewsInterface $compiler;

		/**
		 * Holds the registered compiler instances.
		 *
		 * @var ViewsInterface[]
		 */
		private static array $instances = [];

		/**
		 * Holds the recent paths rendered via load function.
		 *
		 * @var array
		 */
		private static array $tracePaths = [];

		/**
		 * Instantiates a Blade engine instance with the given compiler.
		 *
		 * @param ViewsInterface $compiler
		 * @return self
		 */
		public static function build(ViewsInterface $compiler): self {
			return new self($compiler);
		}

		/**
		 * Compiles the given template content using all registered compiler instances.
		 *
		 * Automatically loads directives if no compiler instances are registered.
		 *
		 * @param string $content The raw template content to compile.
		 * @param array $extract An associative array of data variables to extract into the view.
		 * @return string The compiled output.
		 *
		 * @throws CompilerException
		 */
		public static function compile(string $content, array $extract = []): string {
			$isAssociativeArray = function(array $arr): bool {
				foreach (array_keys($arr) as $key) {
					if (is_string($key)) return true;
				}
				return false;
			};

			if ($extract && !$isAssociativeArray($extract))
				throw new CompilerException("Invalid data passed for extraction: " . json_encode($extract));

			if (empty(self::$instances))
				self::loadDirectives(__DIR__ . '/../directives');

			ob_start();
			foreach (self::$instances as $compiler) {
				/** @var ViewsInterface $compiler */
				$content = $compiler->compile($content);
			}

			self::capture($content, $extract);
			return ob_get_clean();
		}

		/**
		 * Loads all directive files from the specified path.
		 *
		 * @param string $path Absolute path to the directory containing directive files.
		 * @return void
		 *
		 * @throws CompilerException If the given path does not exist or is not a directory.
		 */
		public static function loadDirectives(string $path): void {
			if (!is_dir($path)) {
				throw new CompilerException("Directive path not found: {$path}");
			}

			foreach (glob("{$path}/__*.php") as $file) {
				require_once $file;
			}
		}

		/**
		 * Loads and compiles a view template from the specified file path,
		 * then captures and renders it with the provided data.
		 *
		 * The path is added to the internal trace paths for error tracking.
		 *
		 * @param string $path The file path of the view template to load.
		 * @param array $extract An associative array of data variables to extract into the view.
		 *
		 * @throws CompilerException If the file does not exist or if there is a compilation or rendering error.
		 */
		public static function load(string $path, array $extract = []): void {
			if (!file_exists($path))
				throw new CompilerException("File $path does not exist");

			self::$tracePaths[] = $path;
			$isAssociativeArray = function(array $arr): bool {
				foreach (array_keys($arr) as $key) {
					if (is_string($key)) return true;
				}
				return false;
			};

			if ($extract && !$isAssociativeArray($extract))
				throw new CompilerException("Invalid data passed for extraction: " . json_encode($extract));

			self::capture(self::compile(file_get_contents($path), $extract));
		}

		/**
		 * Captures the compiled content and safely includes it, extracting the given data variables.
		 *
		 * Errors during execution are caught and formatted into a detailed HTML error message,
		 * including stack traces, then thrown as a CompilerException.
		 *
		 * The temporary compiled file is deleted after execution.
		 *
		 * @param string $content The compiled PHP content to execute.
		 * @param array $data An associative array of variables to be extracted into the scope of the included template.
		 *
		 * @throws CompilerException If an error occurs during template execution.
		 */
		private static function capture(string $content, array $data = []): void
		{
			static $reported = false;
			static $errorTraces = [];

			$tempFile = tempnam(sys_get_temp_dir(), 'tpl_') . '.php';
			$realPath = $tempFile;
			try
			{
				file_put_contents($tempFile, $content);
				$__resolvedPath = '';

				(static function () use ($tempFile, $data, &$__resolvedPath) {
					$__resolvedPath = self::$tracePaths[count(self::$tracePaths) - 1] ?? $tempFile;
					extract($data, EXTR_SKIP);
					include $tempFile;
				})();

			} catch (Exception|Error $e) {
				if (empty($errorTraces)) {
					$traces = $e->getTrace();
					$originalTrace = [
						'message'  => $e->getMessage(),
						'code'     => $e->getCode(),
						'file'     => $e->getFile(),
						'line'     => $e->getLine(),
						'previous' => $e->getPrevious()?->getMessage(),
					];

					array_unshift($traces, $originalTrace);
					$errorTraces = [
						'path' => $__resolvedPath,
						'tempPath' => $realPath,
						'message' => $e->getMessage(),
						'line' => $e->getLine(),
						'code' => $e->getCode(),
						'traces' => $traces
					];
				}
			} finally {
				unlink($tempFile);
			}

			if (!empty($errorTraces) && !$reported) {
				$errorTrace = $errorTraces;
				$errorLogsHtml = '';

				// Resolve the file line accurately
				foreach ($errorTrace['traces'] as $trace) {
					if (($trace['file'] ?? '') == $tempFile) {
						$errorTrace['line'] = $trace['line'];
						break;
					}
				}

				foreach ($errorTrace['traces'] as $i => $log) {
					if (isset($log['function'])) {
						$call = ($log['class'] ?? '') . ($log['type'] ?? '') . $log['function'] . '()';
					} else {
						$call = '[no function]';
					}

					$file = $log['file'] ?? 'N/A';
					$line = $log['line'] ?? 'N/A';

					if (($errorTrace['tempPath'] ?? null) === $file) {
						$file = $errorTrace['path'];
					}

					$errorLogsHtml .= <<<HTML
                    <hr />
                    <strong>Error Trace #{$i}:</strong><br>
                    Call: $call<br>
                    File: $file<br>
                    Line: $line<br>
                    HTML;
				}


				// Final UI output
				$uiError = <<<HTML
				<div style="padding:1rem; background:#fff3f3; border:1px solid #ffcccc; color:#a00; font-family:monospace;">
					<strong>Main Error:</strong> {$errorTrace['message']}<br>
					<strong>File:</strong> {$errorTrace['path']}<br>
					<strong>Line:</strong> {$errorTrace['line']}<br>
					{$errorLogsHtml}
				</div>
				HTML;

				$reported = true;
				throw new CompilerException($uiError);
			}
		}

		/**
		 * Constructor to set the compiler instance.
		 *
		 * @param ViewsInterface $compiler
		 */
		public function __construct(ViewsInterface $compiler) {
			$this->compiler = $compiler;
		}

		/**
		 * Registers a group of directives or behavior using a callback.
		 *
		 * @param Closure $callback Callback that receives the Blade instance.
		 * @return void
		 */
		public function register(Closure $callback): void {
			$callback($this);

			// Register after the setup
			self::$instances[] = $this->compiler;
		}

		/**
		 * Registers a single directive with the compiler.
		 *
		 * @param string $directive Name of the directive.
		 * @param Closure $callback Directive callback function.
		 * @return void
		 */
		public function directive(string $directive, Closure $callback): void {
			$this->compiler->directive($directive, $callback);
		}

		/**
		 * Wraps a directive between a prefix and suffix.
		 *
		 * @param string $prefix Starting tag or symbol.
		 * @param string $suffix Ending tag or symbol.
		 * @param Closure $callback Callback that defines the behavior between the wrap.
		 * @return void
		 */
		public function wrap(string $prefix, string $suffix, Closure $callback): void {
			$this->compiler->wrap($prefix, $suffix, $callback);
		}

		/**
		 * Compiles a single piece of template content.
		 *
		 * @param string $content The template content.
		 * @return string The compiled result.
		 * @throws CompilerException
		 */
		public function render(string $content): string {
			ob_start();
			self::capture(self::compile($content));
			return ob_get_clean();
		}

		/**
		 * Attempts to resolve and throw a clearer error message based on the trace.
		 *
		 * @param array $traces The stack trace array from an exception.
		 * @param array $attr Additional attributes, such as 'template' and 'resolvedPath'.
		 *
		 * @throws CompilerException
		 */
		public function resolveError(array $traces, array $attr): void {
			$stop = false;
			$resolvedPath = $attr['resolvedPath'] ?? '';
			$template = $attr['template'] ?? '';

			if (!$resolvedPath) {
				foreach ($traces as $trace) {
					$file = $trace['file'] ?? '';
					$file = explode(DIRECTORY_SEPARATOR, $file);
					$file = array_pop($file);

					if ($stop) {
						$resolvedPath = $trace['args'][0] ?? '';
						break;
					}

					if (in_array($file, ['Blade.php', 'Component.php']) && ($trace['function'] ?? '') === 'compile') {
						$stop = true;
					}
				}
			}

			$title = ucfirst($template);
			throw new CompilerException("`$title` path not found from $resolvedPath");
		}

		/**
		 * Resolves the root path of the project.
		 * Falls back to dirname(__DIR__) if not installed via Composer.
		 *
		 * @param string $path Optional additional path to append.
		 * @return string The resolved absolute path.
		 */
		public function getProjectRootPath(string $path = ''): string {
			if ($path) {
				$path = "/" . ltrim($path, '/');
			}

			$vendorPos = strpos(__DIR__, 'vendor');
			if ($vendorPos !== false) {
				return substr(__DIR__, 0, $vendorPos) . $path;
			}

			return dirname(__DIR__) . $path;
		}
	}
