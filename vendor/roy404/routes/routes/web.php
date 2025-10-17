<?php

use App\Routes\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| Now create something great!
|
*/

Route::get('/', function () {
	$name = 'Robroy';
	$accountUrl = Route::link('account', ['name' => $name]);
	
	echo '<h1>Hello, World!</h1>';
	echo '<p>Welcome to our startup platform. We\'re glad you\'re here.</p>';
	echo '<p><a href="' . htmlspecialchars($accountUrl) . '">Who built this?</a></p>';
});

Route::get('/account/{name}', function(string $name) {
	echo "Hi there, I'm $name!";
})->name('account');
