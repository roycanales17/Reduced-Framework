<?php

    /**
     * Application environment configuration.
     *
     * This closure returns an array of commonly used environment values
     * derived from the server request and project structure.
     *
     * @return void
     */
    return function () {
        // Add more here
        define('DEVELOPMENT', in_array(env('APP_ENV', 'development'), ['development', 'local', 'staging']));
    };
