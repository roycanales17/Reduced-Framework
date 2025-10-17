<?php

	namespace App\Bootstrap\Exceptions;

	use Exception;
	use Throwable;

	class StreamException extends Exception
	{
		protected array $context;

		public function __construct(string $message = "", int $code = 0, array $context = [], Throwable $previous = null)
		{
			$this->context = $context;
			parent::__construct($message, $code, $previous);
		}

		public function getContext(): array
		{
			return $this->context;
		}
	}