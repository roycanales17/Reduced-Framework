<?php
	declare(strict_types=1);
	namespace App\Bootstrap;

	use App\Bootstrap\Exceptions\AppException;
	use App\Bootstrap\Handler\RuntimeException;
	use App\Bootstrap\Helper\BufferedError;
	use App\Bootstrap\Helper\Configuration;
	use App\Bootstrap\Helper\Performance;
	use App\Utilities\Config;
	use App\Utilities\Cache;
	use App\Utilities\Mail;
	use App\Headers\Request;
	use App\Routes\Route;
	use Closure;
	use Exception;
	use Throwable;

	final class Application
	{
		use BufferedError;
		use Configuration;

		private static ?self $app = null;
		private string $envPath = '';
		private Performance $performance;
		private ?RuntimeException $runtimeHandler = null;

		public static function boot(): self {
			if (!isset(self::$app)) {
				self::$app = new self();
			}
			return self::$app;
		}

		public function run(Closure|null $callback = null): void {
			// CLI Checker
			$cli = php_sapi_name() === 'cli';

			try {
				if (!$cli) {
					while (ob_get_level() > 0) {
						ob_end_clean();
					}

					ob_start();
				}

				$this->performance = new Performance(true);
				if ($this->isBufferedError()) {
					throw new AppException($this->getErrorMessage());
				}

				Request::capture();
				Config::load($this->envPath);

				$this->setupConfig();
				$this->setGlobalDefines();
				$this->setDevelopment();
				$this->setDatabaseConfig();
				$this->setSessionConfig();
				$this->setPreloadFiles();
				$this->setScheduler();
				$this->setStreamAuthentication(run: true);

				// Validate/Configure CSRF token
				if (!$cli) {
					define('CSRF_TOKEN', csrf_token());
					validate_token();
				}

				// Configurations
				$conf = $this->getConfig();

				// Configure cache
				if ($cache = $conf['cache']['driver'] ?? '') {
					$cache_attr = $conf['cache'][$cache];
					Cache::configure($cache_attr['driver'], $cache_attr['server'], $cache_attr['port']);
				}

				// Configure mail
				$mail = $conf['mailing'] ?? [];
				if (!empty($mail['enabled'])) {
					$credentials = [];

					if (!empty($mail['username']) && !empty($mail['password'])) {
						$credentials = [
							'username' => $mail['username'],
							'password' => $mail['password'],
						];
					}

					Mail::configure($mail['host'], $mail['port'], $mail['encryption'], $mail['smtp'], $credentials);
				}

				// This display the content page
				if ($callback) $callback($conf);

				// Configure Routes
				foreach ($conf['routes'] ?? [] as $route) {
					$routeObject = Route::configure(
						root: base_path('/routes'),
						routes: $route['routes'] ?? ['web.php'],
						prefix: $route['prefix'] ?? '',
						domain: $route['domain'] ?? config('APP_DOMAIN', 'localhost')
					);

					if ($cli) {
						$routeObject->routes(function($routes) {
							// Store into the artisan
						});
					} else {
						$routeObject->captured($route['captured'], true);
					}
				}

				if (!$cli) {
					if (file_exists(base_path('/views/404.blade.php')) || file_exists(base_path('/views/404.php'))) {
						echo view('404');
					}
					ob_end_flush();

				}

			} catch (Exception|Throwable $e) {
				if (!$cli && Config::get('DEVELOPMENT')) {
					while (ob_get_level() > 0) {
						ob_end_clean();
					}
				}

				if (!$this->runtimeHandler) {
					$this->runtimeHandler = new RuntimeException();
				}

				$this->runtimeHandler->handle($e);
			} finally {
				$this->performance->end();
				if (request()->query('SHOW_PERFORMANCE') === true) {
					print_r($this->performance->generateSummary());
				}
			}
		}

		/**
		 * Set the path to the environment configuration.
		 */
		public function withEnvironment(string $envPath): self
		{
			switch (true) {
				case empty(trim($envPath)):
					$this->throwError('Environment file is required');
					break;

				case !file_exists($envPath):
					$this->throwError('Environment file does not exist');
					break;
			}

			$this->envPath = $envPath;
			return $this;
		}

		public function withStreamAuthentication(array $action): self
		{
			$this->setStreamAuthentication($action);
			return $this;
		}

		/**
		 * Set the path to the application configuration.
		 */
		public function withConfiguration(string $configPath): self
		{
			switch (true) {
				case (empty(trim($configPath))):
					$this->throwError('Configuration file is required');
					break;

				case !file_exists($configPath):
					$this->throwError('Configuration file does not exist');
					break;
			}

			$this->setConfiguration($configPath);
			return $this;
		}

		/**
		 * Handle exception setup via callback.
		 */
		public function withExceptions(Closure $callback): self
		{
			$callback($this->runtimeHandler = new RuntimeException());
			return $this;
		}

		/**
		 * Prevent cloning the singleton instance.
		 * @throws Exception
		 */
		public function __clone()
		{
			throw new Exception("Cloning is not allowed for this singleton.");
		}

		/**
		 * Prevent serialization of the singleton instance.
		 * @throws Exception
		 */
		public function __sleep()
		{
			throw new Exception("Serialization is not allowed for this singleton.");
		}

		/**
		 * Prevent unserialization of the singleton instance.
		 * @throws Exception
		 */
		public function __wakeup()
		{
			throw new Exception("Unserialization is not allowed for this singleton.");
		}
	}
