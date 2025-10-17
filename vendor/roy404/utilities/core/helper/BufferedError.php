<?php

	namespace App\Bootstrap\Helper;

	trait BufferedError
	{
		private string $message = '';
		private int $code = 0;

		protected function throwError(string $message, int $code = 500): void {
			$this->message = $message;
			$this->code = $code;
		}

		protected function isBufferedError(): bool {
			return $this->code && $this->code !== 200;
		}

		protected function getErrorMessage(): string {
			return $this->message;
		}

		protected function getErrorCode(): int {
			return $this->code;
		}
	}