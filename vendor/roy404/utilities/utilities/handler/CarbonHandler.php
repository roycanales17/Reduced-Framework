<?php

	namespace App\Utilities\Handler;

	use DateTimeZone;
	use DateTime;

	abstract class CarbonHandler
	{
		protected DateTime $date;
		protected string $timezone;

		public function __construct(string $time = 'now', string $timezone = 'UTC')
		{
			$this->registerDate($time, $timezone);
		}

		public function addDays(int $days): self
		{
			$this->getDateTime()->modify("+{$days} days");
			return $this;
		}

		public function addMonths(int $months): static
		{
			$this->getDateTime()->modify("+{$months} months");
			return $this;
		}

		public function addYears(int $years): static
		{
			$this->getDateTime()->modify("+{$years} years");
			return $this;
		}

		public function subDays(int $days): self
		{
			$this->getDateTime()->modify("-{$days} days");
			return $this;
		}

		public function subMonths(int $months): static
		{
			$this->getDateTime()->modify("-{$months} months");
			return $this;
		}

		public function subYears(int $years): static
		{
			$this->getDateTime()->modify("-{$years} years");
			return $this;
		}

		public function format(string $format = 'Y-m-d H:i:s'): string
		{
			return $this->getDateTime()->format($format);
		}

		public function toDateString(): string
		{
			return $this->getDateTime()->format('Y-m-d');
		}

		public function toTimeString(): string
		{
			return $this->getDateTime()->format('H:i:s');
		}

		public function diffInDays(self $other): int
		{
			$interval = $this->getDateTime()->diff($other->getDateTime());
			return abs($interval->days);
		}

		public function diffInMonths(self $other): int
		{
			$interval = $this->getDateTime()->diff($other->getDateTime());
			return abs($interval->m + ($interval->y * 12));
		}

		public function isFuture(): bool
		{
			return $this->getDateTime() > new DateTime('now', new DateTimeZone($this->timezone));
		}

		public function isPast(): bool
		{
			return $this->getDateTime() < new DateTime('now', new DateTimeZone($this->timezone));
		}

		public function startOfDay(): static
		{
			$this->getDateTime()->setTime(0, 0, 0);
			return $this;
		}

		public function endOfDay(): static
		{
			$this->getDateTime()->setTime(23, 59, 59);
			return $this;
		}

		public function getDateTime(): DateTime
		{
			return $this->date;
		}

		private function registerDate(string $time, string $timezone): void
		{
			$this->date = new DateTime($time, new DateTimeZone($timezone));
			$this->timezone = $timezone;
		}
	}