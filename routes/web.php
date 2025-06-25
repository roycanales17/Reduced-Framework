<?php

	use App\Routes\Route;

	Route::get('/', function () {
		return view('welcome');
	});

	Route::get('/test', function () {
		return view('welcome');
	});