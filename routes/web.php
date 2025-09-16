<?php

	use App\Routes\Route;

	Route::get('/', function () {
		return view('welcome');
	})
    ->name('homepage');


