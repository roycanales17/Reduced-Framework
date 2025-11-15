<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default Web Routes Configuration
	|--------------------------------------------------------------------------
	|
	| Handles rendering of content responses. If the HTTP status code is 404,
	| the capture will be skipped. Otherwise, content will be injected into
	| the specified Blade template.
	|
	*/
	'web' => [
        'routes' => ['web.php']
	],

	/*
	|--------------------------------------------------------------------------
	| API Routes Configuration
	|--------------------------------------------------------------------------
	|
	| Handles raw output of captured API content.
	|
	*/
	'api' => [
		'routes' => ['api.php'],
		'prefix' => 'api'
	]
];
