<?php

	namespace App\Utilities;

	/**
	 * Class Logger
	 *
	 * A simple file-based logging utility supporting different log levels.
	 */
	final class Logger
	{
		/** @var string The directory where logs will be saved. */
		protected string $logDirectory;

		/** @var string The name of the log file. */
		protected string $logFile;

		/** @var string The minimum log level to write. */
		protected string $logLevel;

		/** @var array Valid log levels in order of severity. */
		protected array $logLevels = ['debug', 'info', 'warning', 'error'];

		/**
		 * Logger constructor.
		 *
		 * @param string $logDirectory Directory to store logs (default: 'logs').
		 * @param string $logFile Log file name (default: 'app.log').
		 * @param string $logLevel Minimum log level to log (default: 'debug').
		 */
		public function __construct(string $logDirectory = 'logs', string $logFile = 'app.log', string $logLevel = 'debug')
		{
			$this->logDirectory = trim($logDirectory, '/');
			$this->logFile = $logFile;
			$this->logLevel = in_array(strtolower($logLevel), $this->logLevels) ? strtolower($logLevel) : 'debug';

			$this->ensureLogDirectoryExists();
		}

		/**
		 * Ensures that the log directory exists.
		 *
		 * @return void
		 */
		protected function ensureLogDirectoryExists(): void
		{
			if (!is_dir($this->logDirectory)) {
				mkdir($this->logDirectory, 0777, true);
			}
		}

		/**
		 * Logs a debug-level message.
		 *
		 * @param string $message
		 * @param array $context
		 * @return void
		 */
		public function debug(string $message, array $context = []): void
		{
			$this->log('debug', $message, $context);
		}

		/**
		 * Logs an info-level message.
		 *
		 * @param string $message
		 * @param array $context
		 * @return void
		 */
		public function info(string $message, array $context = []): void
		{
			$this->log('info', $message, $context);
		}

		/**
		 * Logs a warning-level message.
		 *
		 * @param string $message
		 * @param array $context
		 * @return void
		 */
		public function warning(string $message, array $context = []): void
		{
			$this->log('warning', $message, $context);
		}

		/**
		 * Logs an error-level message.
		 *
		 * @param string $message
		 * @param array $context
		 * @return void
		 */
		public function error(string $message, array $context = []): void
		{
			$this->log('error', $message, $context);
		}

		/**
		 * Formats context array into a readable string for the log.
		 *
		 * @param array $context
		 * @return string
		 */
		protected function formatContext(array $context): string
		{
			if (empty($context)) {
				return '';
			}

			return "\n\n" . print_r($context, true) . "|\n|\n|\n|";
		}

		/**
		 * Gets the full path to the log file.
		 *
		 * @return string
		 */
		protected function getLogFilePath(): string
		{
			return "{$this->logDirectory}/{$this->logFile}";
		}

		/**
		 * Logs a message at the given level with optional context.
		 *
		 * @param string $level
		 * @param string $message
		 * @param array $context
		 * @return void
		 *
		 * @throws \InvalidArgumentException If the level is not valid.
		 */
		public function log(string $level, string $message, array $context = []): void
		{
			if (!in_array($level, $this->logLevels)) {
				throw new \InvalidArgumentException("Invalid log level: {$level}");
			}

			if ($this->shouldLog($level)) {
				$logEntry = $this->formatLogEntry($level, $message, $context);
				file_put_contents($this->getLogFilePath(), $logEntry, FILE_APPEND);
			}
		}

		/**
		 * Determines whether a message at a certain level should be logged.
		 *
		 * @param string $level
		 * @return bool
		 */
		protected function shouldLog(string $level): bool
		{
			$currentLevelIndex = array_search($this->logLevel, $this->logLevels);
			$messageLevelIndex = array_search($level, $this->logLevels);
			return $messageLevelIndex >= $currentLevelIndex;
		}

		/**
		 * Formats the log entry as a string.
		 *
		 * @param string $level
		 * @param string $message
		 * @param array $context
		 * @return string
		 */
		protected function formatLogEntry(string $level, string $message, array $context): string
		{
			$timestamp = Carbon::format();
			$level = strtolower($level);
			$levelUpper = strtoupper($level);

			$icons = [
				'info' => 'ℹ️',
				'error' => '🚨',
				'warning' => '⚠️',
				'debug' => '🐞',
			];

			$icon = $icons[$level] ?? '📌';
			$log = "{$icon} [{$levelUpper}] [{$timestamp}]\n\n";

			if (isset($context['exception'])) {
				$log .= "Type     : {$context['exception']}\n";
			}

			$log .= "Message  : {$message}\n";

			if (isset($context['file'])) {
				$log .= "File     : {$context['file']}\n";
			}
			if (isset($context['line'])) {
				$log .= "Line     : {$context['line']}\n";
			}

			if (!empty($context['context']) && is_array($context['context'])) {
				$log .= "\n🌐 Context:\n";
				$ctx = $context['context'];

				// Find longest key length
				$maxKeyLength = max(array_map('strlen', array_keys($ctx)));

				foreach ($ctx as $key => $value) {
					// Normalize value: arrays/objects -> JSON, null -> "null", booleans -> true/false
					if (is_array($value) || is_object($value)) {
						$value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
					} elseif (is_bool($value)) {
						$value = $value ? 'true' : 'false';
					} elseif ($value === null) {
						$value = 'null';
					}

					// Pad key names for alignment
					$log .= str_pad($key, $maxKeyLength) . " : {$value}\n";
				}
			}

			if (isset($context['trace'])) {
				$log .= "\n🔍 Trace:\n" . trim($context['trace']) . "\n";
			}

			$log .= str_repeat('-', 47) . "\n\n\n\n";

			return $log;
		}
	}
