# Content - Blades Compiler

This library allows you to integrate Blade-like templating functionality into your PHP application. It enables the use of Blade syntax and components, offering a familiar and powerful templating engine for your project.

**Installation**

To get started, install the bundle via Composer:

```
composer require roy404/blades
```

# Blades Feature Documentation

`Blades` is a PHP library designed to provide Blade-inspired templating capabilities. With this library, you can use Blade syntax and custom directives in your PHP applications, facilitating a smooth transition for developers. The library enhances your templating workflow with custom methods and functionalities that mimic Blade's behavior while offering additional flexibility for use outside the Laravel ecosystem.

# Blade Custom Methods Usage Guide

This guide helps you understand when and how to use the `compile`, `loadDirectives`, and `load` methods in your project.

## 🧩 1. `loadDirectives()`

### 🔍 What It Does
- Loads all directive definition files from a given directory.
- Each directive file should define logic extending the compiler.

### ✅ When To Use
- You need to register your own custom directives.
- You want to make sure directives are available before calling `compile()`.

Note: You usually don’t need to call this manually — compile() will automatically invoke it if no directives are loaded.

### 🧪 Example
```php
Blade::loadDirectives(__DIR__ . '/directives');
``` 

## 🧩 2. `compile()`

### 🔍 What It Does
- Compiles raw template content (as a string).
- Applies all registered compiler instances and directives.
- Returns the final compiled output as a string.

### ✅ When To Use
- You want to render template content stored in a variable or database.
- You need to manually post-process or store the compiled output.

### 🧪 Example
```php
$content = '<div>Hello, {{ $name }}!</div>';
echo Blade::compile($content, ['name' => 'Robroy Canales']);
```

## 🧩 3. `load()`

### 🔍 What It Does
- Loads and compiles a `.php` (or template) file.
- Renders it directly with the provided variables.

### ✅ When To Use
- You have a physical file you want to render.
- You want to output the view directly (e.g. from a controller or route handler).

### 🧪 Example
```php
Blade::load(__DIR__ . '/views/profile.php', ['user' => $user]);
```

## 🛠️ 4. `build()`

### 🔍 What It Does
- Initializes a new Blade engine instance using a custom compiler (implementing `ViewsInterface`).
- Returns the instance for chaining, such as registering custom directives.

### ✅ When To Use
- You want to register **custom directives** to extend the template engine.
- You are implementing your own logic inside a compiler class (like a plugin).

### 🧪 Example
```php
Blade::build(new Loops)->register(function (Blade $blade) {
	// Add @break directive
	$blade->directive('break', fn() => '<?php break; ?>');

	// Add @continue directive
	$blade->directive('continue', fn() => '<?php continue; ?>');
});
