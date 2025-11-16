<?php

	namespace Handler\Model;

	use App\Databases\Facade\Model;

	class Users extends Model
	{
		/** @var string Primary key of the table */
		public string $primary_key = 'id';

		/** @var string Table name */
		public string $table = 'users';

		/** @var array Fillable attributes */
		public array $fillable = [];
	}