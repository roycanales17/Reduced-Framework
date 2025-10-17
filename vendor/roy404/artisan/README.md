# Web-Artisan
A custom Artisan-like command runner for web environments. Run your custom Artisan commands from the CLI session.

## Installation
Install via Composer:
```
composer require roy404/artisan
```

## Usage (CLI Mode)

To start a CLI session using Web-Artisan, add the following line to any PHP file:

```php
App\Console\Terminal::capture($argv);
```

Then, run the file using PHP:
```php
php filename.php
```

## Loading Custom Commands
If you want to load additional commands (also known as directives), simply call the `config()` method before invoking `capture()`.

* The first parameter defines the directive group name (e.g., 'commands').
* The second parameter is optional and allows you to specify a custom root path where the directive group is stored.

```php
App\Console\Terminal::config('commands');
App\Console\Terminal::capture($argv);
```

