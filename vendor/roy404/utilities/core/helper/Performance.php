<?php

	namespace App\Bootstrap\Helper;

	use Exception;

	final class Performance
	{
		private float $startTime = 0.0;
		private int $startMemory = 0;

		private float $endTime = 0.0;
		private int $endMemory = 0;

		public function __construct(bool $autoStart = false)
		{
			if ($autoStart) {
				$this->start();
			}
		}

		public function start(): void
		{
			$this->startTime = microtime(true);
			$this->startMemory = memory_get_usage();
		}

		public function end(): void
		{
			$this->endTime = microtime(true);
			$this->endMemory = memory_get_usage();
		}

		/**
		 * Generate performance.
		 *
		 * @throws Exception
		 */
		public function generateSummary(): array
		{
			if ($this->endTime === 0.0) {
				throw new Exception("You must call end() before generating the summary.");
			}

			return [
				'executionTime' => $this->getExecutionTime(),
				'memoryUsage' => $this->getMemoryUsage(),
				'peakMemory' => $this->getPeakMemory(),
			];
		}

		public function getExecutionTime(): float
		{
			return $this->endTime - $this->startTime;
		}

		public function getMemoryUsage(): int
		{
			return $this->endMemory - $this->startMemory;
		}

		public function getPeakMemory(): int
		{
			return memory_get_peak_usage();
		}
	}