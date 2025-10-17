# PHP Utilities Library

A comprehensive collection of essential PHP utilities designed to streamline development and enhance productivity. This library includes versatile tools for common tasks, offering a robust foundation for building efficient and maintainable applications.

**Installation**

```
composer require roy404/utilities
```

## 🚀 Features
This library includes a range of utility classes to handle common development needs:

* **Cache:** Simplified caching interface for fast, temporary storage.
* **Carbon:** Simplified date and time manipulation.
* **Config:** Easy access to configuration files and environment-based settings.
* **Logger:** Lightweight logging system to track events, errors, and debug output.
* **Mail:** Utility for sending emails with flexible transport options.
* **Storage:** Easy-to-use file storage and management with support for local and cloud-based disks like AWS S3.
* **RateLimiter:** Control access frequency and throttle requests effectively.
* **Server:** Useful helpers for interacting with server and request data.
* **Cache:** Lightweight caching system to improve performance and reduce database overhead.
* **Session:** Streamlined session management and flash messaging.
* **Storage:** Unified file storage interface for local or cloud drivers.

This library is modular, lightweight, and optimized for seamless integration into your PHP projects. Whether you're working on a small application or a large-scale system, PHP Utilities Library provides the tools you need to get the job done efficiently.

# Standards Helper Functions

A collection of helper functions used across the application to simplify common tasks such as session management, config access, cookie encryption, view rendering, CLI handling, CSRF protection, and more.

## 🚀 Usage

Below are some common usage examples:

```php
session('user_id');             // Get session value
config('APP_NAME', 'default');  // Get config value and fallback default value
csrf_token();                   // Get or create CSRF token
encrypt('Secret123');           // Encrypt a string
decrypt($encoded);              // Decrypt an encoded string
view('users.profile', [...]);   // Render a view
dump($data, true);              // Debug and halt
```

## 🧩 Function Reference

- `session(string $key): mixed`: Retrieve a session value by key.
- `config(string $key, mixed $default = null): mixed`: Fetch a config value. Auto-loads *.env* if not loaded.
- `csrf_token(): string`: Returns a CSRF token, generating it if not present.
- `encrypt(string $string): string`: Encrypts a string by encoding characters with specific prefixes.
- `decrypt(string $encoded): string`: Reverses the encrypt() function to return the original string.
- `launch_cli_session(array $args, string $path = '', string $root = ''): void`: Starts a CLI session using the *Terminal* class.
- `view(string $path, array $data = []): string`: Renders a PHP or Blade view file and returns the HTML content.
- `dump(mixed $data, bool $exit = false): void`: Pretty prints debug info (HTML formatted). Halts if *$exit* = true.
- `str_limit(string $value, int $limit = 100, string $end = '...'): string`: Limits string length and appends *$end* if truncated.
