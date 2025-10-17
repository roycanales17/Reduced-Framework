<?php

	namespace App\Utilities;

	/**
	 * Class RateLimiter
	 *
	 * Provides IP-based rate limiting functionality with various time-based helpers.
	 * Relies on a caching system to track request counts and expiration.
	 *
	 * @package App\Utilities
	 */
	final class RateLimiter
	{
		/**
		 * Attempts to perform an action under a given rate limit.
		 *
		 * @param string $key       A unique key identifying the action (e.g., route name, endpoint).
		 * @param int    $limit     Maximum allowed attempts within the decay period. Defaults to 10.
		 * @param int    $decayRate Time window in seconds before the rate counter resets. Defaults to 120.
		 *
		 * @return bool True if the action is allowed, false if rate limit exceeded.
		 */
		public static function attempt(string $key, int $limit = 10, int $decayRate = 120): bool
		{
			$ip = Server::IPAddress();
			$cacheKey = "rate:$ip:$key";
			$rateLimit = Cache::get($cacheKey);

			if ($rateLimit === false) {
				Cache::set($cacheKey, $limit - 1, $decayRate);
				return true;
			}

			if ($rateLimit > 0) {
				$expirationTime = Cache::getExpiration($cacheKey);

				if ($expirationTime !== false) {
					Cache::set($cacheKey, $rateLimit - 1, $expirationTime);
					return true;
				} else {
					// Retry if expiration retrieval failed
					return self::attempt($key, $limit, $decayRate);
				}
			}

			return false;
		}

		/**
		 * Limits the action to a number of times per minute.
		 *
		 * @param string $key   Unique identifier for the action.
		 * @param int    $limit Maximum allowed attempts per minute.
		 *
		 * @return bool True if allowed, false otherwise.
		 */
		public static function perMinute(string $key, int $limit = 1): bool
		{
			return self::attempt($key, $limit, 60);
		}

		/**
		 * Limits the action to a number of times per hour.
		 *
		 * @param string $key   Unique identifier for the action.
		 * @param int    $limit Maximum allowed attempts per hour.
		 *
		 * @return bool True if allowed, false otherwise.
		 */
		public static function perHour(string $key, int $limit = 1): bool
		{
			return self::attempt($key, $limit, 60 * 60);
		}

		/**
		 * Limits the action to a number of times per day.
		 *
		 * @param string $key   Unique identifier for the action.
		 * @param int    $limit Maximum allowed attempts per day.
		 *
		 * @return bool True if allowed, false otherwise.
		 */
		public static function perDay(string $key, int $limit = 1): bool
		{
			return self::attempt($key, $limit, 60 * 60 * 24);
		}

		/**
		 * Limits the action to a number of times per month.
		 *
		 * @param string $key   Unique identifier for the action.
		 * @param int    $limit Maximum allowed attempts per month.
		 *
		 * @return bool True if allowed, false otherwise.
		 */
		public static function perMonth(string $key, int $limit = 1): bool
		{
			return self::attempt($key, $limit, 60 * 60 * 24 * 30);
		}
	}