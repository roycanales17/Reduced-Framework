<?php

    use App\Routes\Route;

    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application.
    | These routes handle browser requests and return views or responses.
    | Now create something great!
    |
    */

    Route::get('/', function () {
        return view('welcome');
    });
