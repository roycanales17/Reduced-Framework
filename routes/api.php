<?php

	use App\Routes\Route;

	Route::get('/', function () {
		return response([
			'message' => 'Welcome to Framework API',
			'version' => '1.0.0',
		])->json();
	});