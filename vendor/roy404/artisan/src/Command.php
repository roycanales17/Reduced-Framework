<?php

	namespace App\Console;

	/**
	 * Class Command
	 *
	 * An abstract base class for creating custom console commands.
	 * Provides convenience methods for styled console output,
	 * filesystem operations, user prompts, and command execution.
	 *
	 * Extend this class and implement the `handle()` method
	 * to define your own command logic.
	 *
	 * Example:
	 * ```php
	 * class SendEmails extends Command
	 * {
	 *     protected string $signature = 'emails:send';
	 *     protected string $description = 'Send queued emails to users';
	 *
	 *     public function handle(): void
	 *     {
	 *         $this->info('Starting email send process...');
	 *         // custom logic
	 *         $this->success('All emails sent successfully!');
	 *     }
	 * }
	 * ```
	 */
	abstract class Command
	{
		/**
		 * Write an informational message to the console.
		 *
		 * @param string $message  The message to display.
		 * @param int    $code     Exit code (optional).
		 * @param bool   $return   If true, returns the formatted string instead of printing.
		 * @return string          The formatted message (if $return = true).
		 */
		protected function info(string $message, int $code = 0, bool $return = false): string
		{
			return Terminal::info($message, $code, $return);
		}

		/**
		 * Write an error message to the console.
		 *
		 * @param string $message  The error message.
		 * @param bool   $newLine  Whether to append a new line at the end.
		 * @return void
		 */
		protected function error(string $message, bool $newLine = true): void
		{
			Terminal::error($message, $newLine);
		}

		/**
		 * Write a success message to the console.
		 *
		 * @param string $message  The success message.
		 * @param bool   $newLine  Whether to append a new line at the end.
		 * @return void
		 */
		protected function success(string $message, bool $newLine = true): void
		{
			Terminal::success($message, $newLine);
		}

		/**
		 * Write a warning message to the console.
		 *
		 * @param string $message  The warning message.
		 * @param bool   $newLine  Whether to append a new line at the end.
		 * @return void
		 */
		protected function warn(string $message, bool $newLine = true): void
		{
			Terminal::warn($message, $newLine);
		}

		/**
		 * Get the project root path, optionally appending a relative path.
		 *
		 * @param string $path Relative path to append to the root.
		 * @return string      The full resolved path.
		 */
		protected function root(string $path): string
		{
			return dirname(__DIR__) . ($path ? "/" . trim($path, '/') : '');
		}

		/**
		 * Perform another command by delegating to the Terminal handler.
		 *
		 * If the command is not found, it outputs an error and, if available,
		 * falls back to this command's `execute()` method.
		 *
		 * @param string $command  The command to execute.
		 * @param array  $args     Arguments to pass to the command.
		 * @param bool   $execute  Whether to execute immediately.
		 * @return void
		 */
		protected function perform(string $command, array $args = [], bool $execute = false): void
		{
			if (!Terminal::handle($command, $args, $execute)) {
				$this->error("Command {$command} not found.");

				if (method_exists($this, 'execute')) {
					$this->execute();
				}
			}
		}

		/**
		 * Create a new file with the given content inside a directory.
		 *
		 * @param string $filename   The name of the file.
		 * @param string $content    The file contents.
		 * @param string $directory  Target directory path.
		 * @return bool              True if file created successfully, false otherwise.
		 */
		protected function create(string $filename, string $content, string $directory): bool
		{
			if (!is_dir($directory)) {
				if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
					return false;
				}
			}

			$filePath = $directory . '/' . $filename;

			if (file_exists($filePath)) {
				$this->error("File '{$filename}' already exists.");
				return false;
			}

			return file_put_contents($filePath, $content) !== false;
		}

		/**
		 * Ask the user to confirm an action with a set of options.
		 *
		 * @param string $message The question to ask.
		 * @param array  $opt     Available options (default: ['no', 'yes']).
		 * @return int            Index of the selected option.
		 */
		protected function confirm(string $message, array $opt = ['no', 'yes']): int
		{
			return Terminal::question($message, $opt);
		}

		/**
		 * Get the command signature (name and options).
		 *
		 * @return string
		 */
		public function getSignature(): string
		{
			return $this->signature;
		}

		/**
		 * Get the command description.
		 *
		 * @return string
		 */
		public function getDescription(): string
		{
			return $this->description;
		}

		/**
		 * Execute the command logic.
		 *
		 * Must be implemented by concrete commands.
		 *
		 * @return void
		 */
		abstract public function handle(): void;
	}