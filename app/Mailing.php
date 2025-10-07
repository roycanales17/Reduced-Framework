<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Enable Mailing
	|--------------------------------------------------------------------------
	|
	| Toggle mailing functionality for the application. Set to true to enable
	| email sending or false to disable all outgoing emails.
	|
	*/
	'enabled' => config('MAILING_ENABLED', false),

	/*
	|--------------------------------------------------------------------------
	| Mail Transport (SMTP)
	|--------------------------------------------------------------------------
	|
	| Defines the mailer to use for sending emails. Default is SMTP.
	| Other options like "sendmail", "mailgun", or "log" can also be set.
	|
	*/
	'smtp' => config('MAIL_MAILER', 'smtp'),

	/*
	|--------------------------------------------------------------------------
	| SMTP Host Address
	|--------------------------------------------------------------------------
	|
	| The address of your SMTP server. Common examples:
	| - smtp.mailgun.org
	| - smtp.gmail.com
	|
	*/
	'host' => config('MAIL_HOST', 'smtp.mailgun.org'),

	/*
	|--------------------------------------------------------------------------
	| SMTP Port
	|--------------------------------------------------------------------------
	|
	| The port used to connect to the SMTP server.
	| - 587 for TLS
	| - 465 for SSL
	|
	*/
	'port' => config('MAIL_PORT', '587'),

	/*
	|--------------------------------------------------------------------------
	| Email Encryption Protocol
	|--------------------------------------------------------------------------
	|
	| Encryption method for secure email transmission.
	| Common values: 'tls', 'ssl'
	|
	*/
	'encryption' => config('MAIL_ENCRYPTION', 'tls'),

	/*
	|--------------------------------------------------------------------------
	| SMTP Username & Password
	|--------------------------------------------------------------------------
	|
	| Authentication credentials for your SMTP server.
	|
	*/
	'username' => config('MAIL_USERNAME', ''),
	'password' => config('MAIL_PASSWORD', ''),
];