<?php


	namespace App\Routes\Configurations\Blueprints;

	use App\Routes\Scheme\Buffer;
	use App\Routes\Scheme\Pal;

	trait Prefix
	{
		private array $prefix = [];

		protected function RegisterPrefix(string $prefix): self
		{
			if($prefix)
				$this->prefix[] = trim($prefix, '/');

			return $this;
		}

		protected function DestroyPrefix(): void
		{
			if ($this->getPrefix()) {
				$prefix = Buffer::fetch('prefix');
				if ($prefix) {
					array_pop($prefix);
					Buffer::replace('prefix', $prefix);
				}
			}
		}

		protected function SetupPrefix(): void
		{
			$prefixes = $this->getPrefix();
			if ($prefixes && in_array(strtolower(Pal::baseClassName(get_called_class())), Pal::getRoutes('configurations'))) {
				foreach ($prefixes as $prefix) {
					Buffer::register('prefix', $prefix);
				}
			}
		}

		protected function getPrefix(): array
		{
			return $this->prefix;
		}
	}