<?php

    use App\Routes\Route;

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application.
    | These routes are typically stateless and are prefixed by "api".
    | Build something great!
    |
    */

    Route::get('/', function () {
        return response([
            'message' => 'Welcome to Framework API'
        ])->json();
    });
