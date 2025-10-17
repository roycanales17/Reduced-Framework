<?php

	namespace App\Utilities\Blueprints;

	enum CacheDriver: string {
		case Memcached = 'memcached';
		case Redis = 'redis';
	}