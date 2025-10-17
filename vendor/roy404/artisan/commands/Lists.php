<?php

	namespace Commands;

	use App\Console\Command;
	use App\Console\Terminal;

	class Lists extends Command
	{
		protected string $signature = 'list';
		protected string $description = 'Displays all the available methods';

		public function handle(): void
		{
			$grouped = [];
			$commands = Terminal::fetchAllCommands();

			foreach ($commands as $command) {
				$signature = $command['signature'];
				$description = $command['description'];

				if (strpos($signature, ':') !== false) {
					[$group, $sub] = explode(':', $signature, 2);
					if ($sub) {
						$padded = $this->info(str_pad($signature, 35), Terminal::GREEN, return: true);
						$grouped[$group][] = "{$padded}{$description}";
					}
				} else {
					$grouped["_$signature"] = str_pad($signature, 37) . $this->info($description, return: true);
				}
			}

			$this->info("Available Commands:", Terminal::YELLOW);

			$regrouped = [];
			foreach ($grouped as $group => $subCommands) {
				if (is_string($subCommands)) {
					$regrouped[$group] = $subCommands;
				}
			}

			if ($regrouped)
				$regrouped[' '] = '';

			foreach ($grouped as $group => $subCommands) {
				if (!is_string($subCommands)) {
					$regrouped[$group] = $subCommands;
				}
			}

			foreach ($regrouped as $group => $subCommands) {
				if (is_string($subCommands)) {
					$this->info("  {$subCommands}", Terminal::BLUE);
				} else {
					$this->info("  " . $group, Terminal::BLUE);
					foreach ($subCommands as $sub) {
						$this->info("    {$sub}");
					}
				}
			}

			echo "\n";
		}

		public function execute(): void
		{
			Terminal::input(function($args) {
				preg_match_all('/("[^"]*"|\'[^\']*\'|\S+)/', trim($args), $matches);
				$args = array_map(fn($v) => trim($v, '\'"'), $matches[0]);

				$command = $args[0] ?? '';
				$params = array_slice($args, 1);

				$this->perform($command, $params, true);
			});
		}

		public static function retrieveLists(): array
		{
			$grouped = [];
			$commands = Terminal::fetchAllCommands();

			foreach ($commands as $command) {
				$signature = $command['signature'];
				if (strpos($signature, ':') !== false) {
					[$group, $sub] = explode(':', $signature, 2);
					if ($sub) {
						if (!isset($grouped[$group]))
							$grouped[$group] = [];

						$grouped[$group][] = $signature;
					}
				} else {
					if (!isset($grouped[$signature])) {
						$grouped["_$signature"] = [];
					}
				}
			}

			$group_1 = [];
			$group_2 = [];
			foreach ($grouped as $group => $subCommands) {
				if (empty($subCommands)) {
					$group_1[] = str_replace('_', ' ', $group);
				} else {
					foreach ($subCommands as $subCommand) {
						$group_2[] = $subCommand;
					}
				}
			}

			return array_merge($group_1, $group_2);
		}
	}