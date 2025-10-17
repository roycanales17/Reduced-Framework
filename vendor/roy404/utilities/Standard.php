<?php

	use App\Headers\Request;
	use App\Console\Terminal;
	use App\Utilities\Config;
	use App\Utilities\Session;
	use App\View\Compilers\Blade;
	use App\Utilities\Handler\StreamHandler;
	use App\Bootstrap\Exceptions\StreamException;
	use App\View\Compilers\scheme\CompilerException;

	/**
	 * Generates a URI for a named route with optional parameter replacements.
	 *
	 * Replaces placeholders (e.g., `{id}`) in the route URI with values from $params.
	 * If the named route is not found, returns `'/'` as fallback.
	 *
	 * @param string $name           The name of the route.
	 * @param array<string, mixed> $params Associative array of parameters to substitute in the URI.
	 * @return string                The generated URI or `'/'` if the route was not found.
	 */
	function route(string $name, array $params = []): string
	{
		return App\Routes\Route::link($name, $params);
	}

	/**
	 * Retrieves a session value by key.
	 *
	 * @param string $key
	 * @return mixed
	 */
	function session(string $key): mixed
	{
		return Session::get($key);
	}

	/**
	 * Retrieves a configuration value by key.
	 *
	 * @param string $key     The configuration key or constant name.
	 * @param mixed|null $default The default value to return if the key is not found.
	 * @return mixed
	 */
	function config(string $key, mixed $default = null): mixed
	{
		if (Config::isEmpty()) {
			Config::load('.env');
		}

		return Config::get($key, $default);
	}

	/**
	 * Returns a CSRF token stored in the session.
	 *
	 * @return string
	 * @throws Exception
	 */
	function csrf_token(): string
	{
		Session::start();

		if (!Session::has('csrf_token'))
			Session::set('csrf_token', bin2hex(random_bytes(32)));

		return Session::get('csrf_token');
	}

	/**
	 * Encrypts a string by encoding each character with a prefix.
	 *
	 * @param string $string The plain text to encrypt.
	 * @return string The encrypted string format.
	 */
	function encrypt(string $string): string
	{
		$numbers = [];

		foreach (mb_str_split($string) as $char) {
			$ord = ord($char);
			if (ctype_lower($char)) {
				$numbers[] = 'L' . ($ord - ord('a') + 5);
			} elseif (ctype_upper($char)) {
				$numbers[] = 'U' . ($ord - ord('A') + 5);
			} elseif (ctype_digit($char)) {
				$numbers[] = 'D' . $char;
			} else {
				// Escape non-alphanumerics using hex
				$numbers[] = 'X' . bin2hex($char);
			}
		}

		return implode('-', $numbers);
	}

	/**
	 * Decrypts a string that was encrypted with `encrypt()`.
	 *
	 * @param string $encoded The encoded string to decode.
	 * @return string The original plain text.
	 */
	function decrypt(string $encoded): string
	{
		$parts = explode('-', $encoded);
		$decoded = '';

		foreach ($parts as $part) {
			$prefix = $part[0];
			$data = substr($part, 1);

			if ($prefix === 'L') {
				$decoded .= chr((int)$data + ord('a') - 5);
			} elseif ($prefix === 'U') {
				$decoded .= chr((int)$data + ord('A') - 5);
			} elseif ($prefix === 'D') {
				$decoded .= $data;
			} elseif ($prefix === 'X') {
				$decoded .= hex2bin($data);
			}
		}

		return $decoded;
	}

	/**
	 * Launches a CLI session using the internal Terminal class.
	 *
	 * @param array  $args The CLI arguments passed in (e.g., from $_SERVER['argv']).
	 * @param string $path Optional command config path.
	 * @param string $root Optional root directory.
	 * @return void
	 */
	function launch_cli_session(array $args, string $path = '', string $root = ''): void
	{
		if ($path)
			Terminal::config($path, $root);

		Terminal::config('commands', __DIR__);
		Terminal::capture($args);
	}

	/**
	 * Renders a PHP or Blade view file and returns the rendered content.
	 *
	 * @param string $path View path using dot notation (e.g., 'users.profile').
	 * @param array $data Data to be extracted and passed into the view.
	 * @return string Rendered HTML content.
	 * @throws CompilerException
	 */
	function view(string $path, array $data = []): string
	{
		ob_start();
		$normalizedPath = preg_replace('/\.php$/', '', trim(str_replace('.', '/', $path), '/'));
		$mainPath = "../views/{$normalizedPath}.php";
		$bladePath = "../views/{$normalizedPath}.blade.php";

		if (file_exists($bladePath)) {
			$mainPath = $bladePath;
		}

		Blade::load($mainPath, $data);
		return ob_get_clean();
	}

	/**
	 * Outputs variable contents (formatted), only in development mode.
	 * Optionally halts script execution.
	 *
	 * @param mixed $data Data to display (array, object, string, etc.).
	 * @param bool  $exit Whether to call `exit()` after dumping.
	 * @return void
	 */
	function dump(mixed $data, bool $exit = false): void
	{
		if (config('DEVELOPMENT')) {
			$printed = print_r($data, true);
			echo <<<HTML
				<pre> 
					$printed
				</pre>
			HTML;
		}

		if ($exit) exit;
	}

	/**
	 * Validates CSRF token for requests other than GET, HEAD, or OPTIONS.
	 *
	 * Responds with HTTP 400 and JSON message on failure.
	 *
	 * @return void
	 */
	function validate_token(): void
	{
		$method = Request::method();

		if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
			$token = Session::get('csrf_token');
			$requestToken = request()->header('X-CSRF-TOKEN') ?? request()->input('csrf-token');

			if ($requestToken !== $token) {
				$message = Config::get('DEVELOPMENT') ? 'Invalid token' : 'Bad Request';
				exit(response(['message' => $message], 400)->json());
			}
		}
	}

	/**
	 * Limit a string to a given length, appending an ending if truncated.
	 *
	 * @param string $value The input string
	 * @param int $limit Maximum length allowed
	 * @param string $end String to append if truncated (default '...')
	 * @return string The limited string
	 */
	function str_limit(string $value, int $limit = 100, string $end = '...'): string
	{
		if (mb_strlen($value) <= $limit) {
			return $value;
		}

		return rtrim(mb_substr($value, 0, $limit)) . $end;
	}

	/**
	 * Used to render both normal views and "stream" components.
	 *
	 * @param string|null $class Class to invoke
	 * @param array $constructParams Parameters to pass to the class constructor (if needed).
	 * @param bool $asynchronous Whether the stream should run asynchronously.
	 * @return StreamHandler            Rendered HTML output or Stream actions methods for blade content.
	 * @throws StreamException
	 */
	function stream(null|string $class = null, array $constructParams = [], bool $asynchronous = false): StreamHandler
	{
		return new StreamHandler($class, $constructParams, $asynchronous);
	}

	/**
	 * Create a cookie with a default 'custom:' prefix in its name.
	 *
	 * @param string $name Cookie base name
	 * @param mixed $value Value to set in cookie
	 * @param int $expire Expiration time in seconds
	 * @return mixed Result of cookie() function
	 */
	function createCookie(string $name, mixed $value = null, int $expire = 3600): mixed
	{
		return cookie(config('COOKIE_PREFIX', 'custom').":$name", $value, $expire);
	}

	/**
	 * Fetch a cookie with a default 'custom:' prefix in its name.
	 * Returns $default if cookie is not found.
	 *
	 * @param string $name Cookie base name
	 * @param mixed $default Default value if cookie not found
	 * @return mixed Cookie value or default
	 */
	function fetchCookie(string $name, mixed $default = false): mixed
	{
		return cookie(config('COOKIE_PREFIX', 'custom').":$name") ?? $default;
	}

	/**
	 * Delete a cookie with a default 'custom:' prefix in its name.
	 *
	 * @param string $name Cookie base name
	 * @return mixed Result of cookie() function
	 */
	function deleteCookie(string $name): mixed
	{
		return cookie(config('COOKIE_PREFIX', 'custom').":$name", null, -1);
	}

	/**
	 * Set, get, or delete encrypted cookies.
	 *
	 * - If $value is null and $expire is 0, returns the decrypted cookie value.
	 * - If $value is null and $expire is negative, deletes the cookie.
	 * - Otherwise, sets an encrypted cookie with the given value and expiration.
	 *
	 * Encryption uses AES-256-CBC with a key from config('APP_COOKIE_PASSWORD').
	 *
	 * @param string $name Cookie name
	 * @param mixed $value Value to set, or null to get/delete
	 * @param int $expire Expiration time in seconds; 0 for session, negative to delete
	 * @param string $path Cookie path
	 * @param string $domain Cookie domain
	 * @param bool $secure Secure flag (HTTPS only)
	 * @param bool $httponly HttpOnly flag
	 * @return mixed Decrypted cookie value on get, true on set/delete, or null if not found
	 */
	function cookie(string $name, $value = null, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true): mixed
	{
		$key = config('APP_COOKIE_PASSWORD', 'f2dg23asd3141saf');
		$cipher = 'AES-256-CBC';

		$encrypt = function ($data) use ($cipher, $key) {
			$iv = random_bytes(openssl_cipher_iv_length($cipher));
			$json = json_encode($data); // Safely handle arrays, ints, strings, etc.
			$encrypted = openssl_encrypt($json, $cipher, $key, 0, $iv);
			return base64_encode($iv . $encrypted);
		};

		$decrypt = function ($data) use ($cipher, $key) {
			$decoded = base64_decode($data);
			if (!$decoded) return null;

			$ivlen = openssl_cipher_iv_length($cipher);
			$iv = substr($decoded, 0, $ivlen);
			$encrypted = substr($decoded, $ivlen);
			$decrypted = openssl_decrypt($encrypted, $cipher, $key, 0, $iv);
			return json_decode($decrypted, true); // Decoded back to array/int/etc.
		};

		// GET
		if ($value === null && $expire === 0) {
			return isset($_COOKIE[$name]) ? $decrypt($_COOKIE[$name]) : null;
		}

		// REMOVE
		if ($value === null && $expire < 0) {
			setcookie($name, '', time() - 3600, $path, $domain, $secure, $httponly);
			unset($_COOKIE[$name]);
			return true;
		}

		// SET
		$encrypted = $encrypt($value);
		setcookie($name, $encrypted, $expire > 0 ? time() + $expire : 0, $path, $domain, $secure, $httponly);
		$_COOKIE[$name] = $encrypted;

		return true;
	}

	/**
	 * Get the base path of the project.
	 *
	 * @param string $path Optional subpath to append.
	 * @return string
	 */
	function base_path(string $path = ''): string
	{
		$base = dirname(realpath(__DIR__ . '/../../'));
		return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
	}
