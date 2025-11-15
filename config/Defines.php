<?php

/**
 * Application Defines
 *
 * This file contains global constants used throughout the application.
 * Use this file to define environment-specific flags or other global settings.
 */

// Determine if the current environment is a development-like environment
define('DEVELOPMENT', in_array(env('APP_ENV', 'development'), ['development', 'local', 'staging']));
