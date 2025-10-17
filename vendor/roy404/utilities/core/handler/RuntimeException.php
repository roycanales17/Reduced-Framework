<?php

	namespace App\Bootstrap\Handler;

	use App\Utilities\Config;
	use App\Utilities\Logger;
	use App\Utilities\Server;
	use App\Utilities\Mail;
	use App\Headers\Request;
	use ReflectionException;
	use ReflectionFunction;
	use Throwable;
	use Closure;

	final class RuntimeException
	{
		protected array $reportCallbacks = [];
		protected array $dontReport = [];
		protected bool $suppressDuplicates = false;
		protected static array $reportedHashes = [];

		/**
		 * Register a callback to handle a specific type of exception.
		 * @throws ReflectionException
		 */
		public function report(Closure $closure): void {
			$ref = new ReflectionFunction($closure);
			$params = $ref->getParameters();

			if (isset($params[0]) && $type = $params[0]->getType()) {
				$this->reportCallbacks[$type->getName()] = $closure;
			}
		}

		/**
		 * Set a view file to be rendered with exception data.
		 */
		public function view(string $path, array $extract = []): void {
			if (file_exists($path)) {
				view($path, array_merge($extract, ['exception' => $this]));
			}
		}

		/**
		 * Prevent duplicate exceptions from being reported multiple times.
		 */
		public function dontReportDuplicates(): void {
			$this->suppressDuplicates = true;
		}

		/**
		 * Exclude specific exception classes from being reported.
		 */
		public function dontReport(array $exclude = []): void {
			$this->dontReport = array_merge($this->dontReport, $exclude);
		}

		/**
		 * Run the report logic if applicable.
		 */
		public function handle(Throwable $e): void {
			$class = get_class($e);
			$cli = PHP_SAPI === 'cli';

			// Skip if in exclusion list
			foreach ($this->dontReport as $excluded) {
				if ($e instanceof $excluded) {
					return;
				}
			}

			// Skip if duplicate and suppression is enabled
			if ($this->suppressDuplicates) {
				$hash = md5($class . $e->getMessage() . $e->getFile() . $e->getLine());
				if (in_array($hash, self::$reportedHashes, true)) {
					return;
				}
				self::$reportedHashes[] = $hash;
			}

			// Dispatch to registered handler if available
			foreach ($this->reportCallbacks as $type => $callback) {
				if ($e instanceof $type) {
					echo($callback($e));
					return;
				}
			}

			// If the exception has a custom report method, use it
			if (method_exists($e, 'report')) {
				echo($e->report());
				exit();
			}

			// Always put on the logger by default
			$basePath = Config::get('APP_ROOT', '');
			$logger = new Logger($basePath . '/logs', logFile: 'error.log');

			$context = [];
			if (!$cli) {

				// Send via email
				if (class_exists('\Mails\ErrorReportMail')) {
					Mail::mail(new \Mails\ErrorReportMail($e));
				}

				$context = [
					// 🚨 Core request info (always check first)
					'url'           => Server::RequestURI(),
					'method'        => Server::RequestMethod(),
					'ip'            => Server::IPAddress(),
					'host'          => Server::HostName(),
					'protocol'      => Server::Protocol(),
					'secure'        => Server::IsSecureConnection(),
					'is_ajax'       => Server::isAjaxRequest(),
					'request_id'    => Server::RequestId(),
					'response_code' => http_response_code(),

					// ⏱ Timing + connection
					'request_time' => sprintf(
						"%s [%s]",
						Server::RequestTime(),
						date('Y-m-d H:i:s', Server::RequestTime())
					),
					'client_port'   => Server::ClientPort(),
					'server_ip'     => Server::ServerIPAddress(),

					// 🌐 Request metadata
					'referer'       => Server::Referer(),
					'content_type'  => Server::ContentType(),

					// 👤 User/session
					'session_id'    => session_id() ?: null,
					'user_id'       => $_SESSION['user_id'] ?? null,

					// 🔎 Request params (can be verbose but useful)
					'get'           => $_GET ?? [],
					'post'          => $_POST ?? [],
					'query'         => Server::QueryString(),

					// 📝 Potentially long fields (put at the bottom)
					'raw_body'      => file_get_contents('php://input'),
					'accept'        => Server::Accept(),
					'user_agent'    => Server::UserAgent(),
				];
			}

			$logger->error(strip_tags($e->getMessage()), [
				'exception' => strtoupper($class),
				'file'      => $e->getFile(),
				'line'      => $e->getLine(),
				'trace'     => $e->getTraceAsString(),
				'context'   => $context
			]);

			// Display the error only in the development mode
			if (Config::get('DEVELOPMENT')) {
				$file = urlencode($e->getFile());
				$line = $e->getLine();

				// Define editor URL schemes
				$editorUrls = [
					'phpstorm' => "phpstorm://open?file=$file&line=$line",
					'vscode'   => "vscode://file/$file:$line",
					'sublime'  => "subl://open?file=$file&line=$line"
				];
				$selectedUrl = $editorUrls[$e->preferredIDE ?? 'phpstorm'] ?? $editorUrls['vscode'];

				$table = [
					'Exception Type:'      => strtoupper($class),
					'Message:'             => $e->getMessage(),
					'File:'                => $e->getFile(),
					'Line:'                => $e->getLine(),
					'Error Code:'          => $e->getCode(),
					'Previous Exception:'  => ($e->getPrevious() ? $e->getPrevious()->getMessage() : 'None'),
				];

				if ($cli) {
					echo "\n\033[41;37m " . $class . " \033[0m\n\n";

					foreach ($table as $label => $value) {
						echo "\033[33m$label\033[0m $value\n";
					}

					echo "\n\033[31m--- Stack Trace ---\033[0m\n";
					echo $e->getTraceAsString() . "\n";

					echo "\n\033[36mNavigate in editor:\033[0m $selectedUrl\n\n";
				} else {
					if (Server::isAjaxRequest()) {

						$req = new Request();
						$response = [
							'status' => 'error',
							'error'  => [
								'type'    => strtoupper($class),
								'message' => $e->getMessage(),
								'code'    => $e->getCode(),
								'file'    => $e->getFile(),
								'line'    => $e->getLine(),
								'trace'   => $e->getTrace(),
							],
						];

						$headers = [
							'Cache-Control' => 'no-cache, no-store, must-revalidate',
							'Pragma'        => 'no-cache',
							'Expires'       => '0',
							'X-Request-ID'  => Server::RequestId() ?: uniqid('req_', true),
						];

						echo $req->response($response, 500, $headers)->json();
					} else {
						echo '<div style="font-family: Arial, sans-serif; background-color: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;">';
						echo '<h2 style="color: #721c24;">Exception Details</h2>';
						echo '<hr style="border-color: #f5c6cb;">';
						echo '<table style="width: 100%; border-collapse: collapse;">';

						foreach ($table as $label => $value) {
							echo '<tr>';
							echo '<td style="padding: 8px; border: 1px solid #f5c6cb; background-color: #f8d7da;"><strong>' . $label . '</strong></td>';
							echo '<td style="padding: 8px; border: 1px solid #f5c6cb; background-color: #f8d7da;">' . $value . '</td>';
							echo '</tr>';
						}

						echo '</table>';
						echo '<h3 style="color: #721c24;">Stack Trace</h3>';
						echo '<pre style="background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd; border-radius: 5px; color: #333;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
						echo '<a href="' . $selectedUrl . '" style="display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #721c24; color: #fff; text-decoration: none; border-radius: 5px;">Navigate Error</a>';
						echo '</div>';
					}
				}
			} else {
				echo(view('error', ['email' => Config::get('APP_EMAIL', '')]));
			}
		}
	}
