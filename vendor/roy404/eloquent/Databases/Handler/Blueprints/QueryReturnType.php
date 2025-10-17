<?php

	namespace App\Databases\Handler\Blueprints;

	/**
	 * Enum representing the different types of query return values.
	 */
	enum QueryReturnType: string
	{
		/**
		 * Return all rows as an array of associative arrays.
		 */
		case ALL = 'all';

		/**
		 * Return the number of rows affected by the query
		 * (INSERT, UPDATE, DELETE).
		 */
		case ROW_COUNT = 'rowCount';

		/**
		 * Return the ID of the last inserted row.
		 */
		case LAST_INSERT_ID = 'lastInsertId';

		/**
		 * Return a count of rows matching the query conditions.
		 */
		case COUNT = 'count';
	}