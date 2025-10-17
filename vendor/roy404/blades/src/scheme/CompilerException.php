<?php

	namespace App\View\Compilers\Scheme;

	use Exception;

	/**
	 * Exception thrown for errors that occur during view compilation.
	 */
	class CompilerException extends Exception
	{
		/**
		 * Create a new CompilerException.
		 *
		 * @param string $message Error message.
		 * @param int $code Error code.
		 * @param \Throwable|null $previous Previous exception for chaining.
		 */
		public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
		{
			http_response_code(500);
			parent::__construct($message, $code, $previous);
		}

		/**
		 * Exception for invalid wrapper configuration.
		 *
		 * @param string $tag
		 * @return self
		 */
		public static function invalidWrapper(string $tag): self
		{
			return new self("Wrap method requires $tag.");
		}
	}
