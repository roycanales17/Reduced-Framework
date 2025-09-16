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
		'captured' => function (string $content, int $code) {
			if ($code == 404) {
                $content = view('404');
            }

			echo(view('template', [
				'g_page_lang' => config('APP_LANGUAGE'),
				'g_page_title' => config('APP_NAME'),
				'g_page_url' => config('APP_URL'),
				'g_page_description' => "Page description here",
				'g_page_content' => $content
			]));
		}
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
		'prefix' => 'api',
		'captured' => function (string $content) {
			echo($content);
		}
	]
];
