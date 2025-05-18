<?php

	use App\Routes\Route;

	Route::get('/', function () {
		return stream('home', ['welcome' => 'Hello Robroy!']);
	});