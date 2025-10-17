<?php

	namespace App\Utilities\Handler;

	use App\Databases\Database;
	use App\Utilities\Server;
	use SessionHandlerInterface;

	class DatabaseSessionHandler implements SessionHandlerInterface
	{
		protected string $table = 'sessions';

		public function open($path, $name): bool
		{
			return true;
		}

		public function close(): bool
		{
			return true;
		}

		public function read($id): string
		{
			$data = Database::table($this->table)
				->select('data')
				->where('id', $id)
				->field();

			return $data ?? '';
		}

		public function write($id, $data): bool
		{
			$ip = Server::IPAddress();
			$now = date('Y-m-d H:i:s');

			$isExist = Database::table($this->table)->where('id', $id)->exists();

			if ($isExist) {
				Database::table($this->table)
					->where('id', $id)
					->set('data', $data)
					->set('ip_address', $ip)
					->set('last_activity', $now)
					->update();
			} else {
				Database::create($this->table, [
					'id' => $id,
					'data' => $data,
					'ip_address' => $ip,
					'last_activity' => $now
				]);
			}

			return true;
		}

		public function destroy($id): bool
		{
			return (bool) Database::table($this->table)
				->where('id', $id)
				->delete();
		}

		public function gc($max_lifetime): int|false
		{
			$threshold = date('Y-m-d H:i:s', time() - $max_lifetime);

			return (bool) Database::table($this->table)
				->where('last_activity', '<', $threshold)
				->delete();
		}
	}