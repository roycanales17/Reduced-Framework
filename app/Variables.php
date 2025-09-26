<?php

use App\Utilities\Server;

/**
 * Application environment configuration.
 *
 * This closure returns an array of commonly used environment values
 * derived from the server request and project structure.
 *
 * @return array<string, mixed>
 */
return (function () {
	$scheme = Server::IsSecureConnection() ? 'https' : 'http';
	$host   = Server::HostName();
	$uri    = Server::RequestURI();
	$root   = PHP_SAPI === 'cli' ? './' : '../';
	$url    = "$scheme://$host";

	return [
		'APP_HOST'      => $host,
		'APP_SCHEME'    => $scheme,
		'APP_URI_PARAMS'=> $uri,
		'APP_ROOT'      => $root,
		'APP_PUBLIC'    => rtrim($root, '/\\') ."/public",
		'APP_URL'       => $url,
		'APP_FULL_URL'  => "$url$uri",
		'DEVELOPMENT'   => in_array(config('APP_ENV'), ['development', 'local', 'staging'])
	];
})();
