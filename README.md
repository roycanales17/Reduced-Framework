# PHP Framework Documentation

Welcome to the official documentation for the **PHP Framework** — a modern, lightweight, and developer-friendly web application framework designed for speed, simplicity, and scalability.

This guide will walk you through installation, configuration, and usage of the framework, along with its key components and features.

### Core Services & Technologies

This project uses a modern containerized architecture powered by Docker, combining application code, databases, caching, queues, and local AWS emulation.

**Languages & Frameworks**

![PHP](https://img.shields.io/badge/PHP-8.2%2B-8892BF?logo=php&logoColor=white)
![Blade](https://img.shields.io/badge/Blade-FF2D20?logo=laravel&logoColor=white)
![SCSS](https://img.shields.io/badge/SCSS-CC6699?logo=sass&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-38B2AC?logo=tailwind-css&logoColor=white)
![Xdebug](https://img.shields.io/badge/Xdebug-2C873F?logo=php&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=white)

**Infrastructure & Services**
- **MySQL 8** – Relational database
- **phpMyAdmin** – Database management UI
- **Redis (Alpine)** – Cache & queue driver
- **Memcached (Alpine)** – Alternative caching layer
- **Mailhog** – Local email testing (SMTP + Web UI)
- **Cron** – Scheduled tasks runner
- **LocalStack** – Local AWS services (S3, SQS, SNS, Lambda, DynamoDB)

This setup provides a full-featured development environment that mirrors production as closely as possible, while staying lightweight and developer-friendly.

---

## 📚 Table of Contents

- [Getting Started](#-getting-started)
    - [Installation](#installation)
    - [Server Requirements](#server-requirements)
- [Configuration](#-configuration)
    - [Environment File](#environment-file)
    - [Docker Setup](#docker-setup)
    - [Available Services](#available-services)
    - [Xdebug Integration](#xdebug-integration)
- [Framework Overview](#-framework-overview)
    - [Routing](#routing)
    - [Controllers](#controllers)
    - [Views & Blade Templates](#views--blade-templates)
    - [Models & Eloquent ORM](#models--eloquent-orm)
    - [Artisan CLI](#artisan-cli)
    - [StreamWire](#streamwire)
- [Advanced Topics](#-advanced-topics)
    - [Cron Jobs & Scheduler](#cron-jobs--scheduler)
    - [LocalStack & AWS Integration](#localstack--aws-integration)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🚀 Getting Started

### Installation

Install the framework using Composer:

```bash
composer create-project roy404/framework project-name 
```

Once installed, navigate to your project directory and start the local development server:

```bash 
php artisan serve 
```

---

### Server Requirements

- PHP 8.2 or higher
- Composer
- Docker & Docker Compose (for containerized setup)
- MySQL or PostgreSQL database (recommended)

---

## ⚙️ Configuration

### Environment File

Update the `.env` file with your database credentials, app URL, and other configuration values.

### Docker Setup

Start the development environment using Docker:

```bash 
docker-compose up --build -d 
```

Stop containers:

```bash 
docker-compose down 
```

---

### Available Services

Your development environment comes preconfigured with the following services:

- **app** → The main PHP application container (`/var/www/html`).
- **mysql** → MySQL database service.
- **phpmyadmin** → Web interface for managing MySQL (`http://localhost:8080`).
- **memcached** → Caching service to speed up application performance.
- **mailhog** → SMTP testing tool with a web UI at (`http://localhost:8025`).
- **redis** → In-memory data store for caching, queues, and sessions.
- **cron** → Dedicated service for running scheduled tasks.
- **localstack** → Local AWS cloud emulator (S3, SNS, SQS, etc).
- **xdebug** → Debugging and profiling extension for PHP, integrated into the `app` container.

---

### Xdebug Integration

Verify **Xdebug** is running inside the container:

```bash 
docker exec -it app_container tail -f /tmp/xdebug/xdebug.log 
```

---

## 🏗 Framework Overview

The framework provides a modular architecture with the following core components.

### Routing

Define routes in the `routes/web.php` file:

```php
use App\Routes\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users/{id}', [UserController::class, 'show']);
```

#### Routing Methods:

The framework provides a fluent, expressive API for defining routes.  
Below are the available static methods for configuring routes:

- **put(string \$uri, string|array|Closure $action = [])**  
  Defines a `PUT` route.

- **patch(string \$uri, string|array|Closure \$action = [])**  
  Defines a `PATCH` route.

- **delete(string \$uri, string|array|Closure \$action = [])**  
  Defines a `DELETE` route.

- **get(string \$uri, string|array|Closure \$action = [])**  
  Defines a `GET` route.

- **post(string \$uri, string|array|Closure \$action = [])**  
  Defines a `POST` route.

- **group(array \$attributes, Closure \$action)**  
  Registers a group of routes with shared configurations and middleware, enhancing route organization and reusability.

- **controller(string \$className)**  
  Registers a controller to handle the route actions.

- **middleware(string|array $action)**  
  Assigns middleware to the route for request filtering.

- **prefix(string \$prefix)**  
  Adds a URI prefix to all routes in the group.

- **name(string \$name)**  
  Assigns a name to the route, useful for generating URLs.

- **domain(string|array \$domain)**  
  Binds the route to a specific domain or subdomain.

- **where(string \$key, string \$expression)**  
  Defines a regular expression constraint for a route parameter.

## Cron Scheduler

The **Scheduler** provides a route-like API for defining and managing recurring tasks using cron expressions.  
All schedules are defined in `routes/cron.php`.

### Sample Schedules

```php
use App\Console\Schedule;

Schedule::command('clear:logs')->daily();
Schedule::command('emails:send')->everyFiveMinutes();
Schedule::command('report:generate')->at('14:30');
```

### Available Frequency Methods

The scheduler provides expressive helpers for defining task frequency:

| Method                  | Description                                              |
|--------------------------|----------------------------------------------------------|
| `everyMinute()`          | Run the task every minute.                              |
| `everyFiveMinutes()`     | Run the task every 5 minutes.                           |
| `hourly()`               | Run the task every hour.                                |
| `daily()`                | Run the task daily at midnight.                         |
| `weekly()`               | Run the task weekly on Sunday at midnight.              |
| `monthly()`              | Run the task monthly on the 1st at midnight.            |
| `yearly()`               | Run the task yearly on January 1st at midnight.         |
| `cron($expression)`      | Use a custom cron expression (e.g., `0 6 * * 1-5`).     |
| `at('HH:MM')`            | Run the task daily at a specific time.                  |

### Controllers

Controllers handle request/response logic:

```php
namespace Http\Controller;

use App\Http\Controller;
use App\Headers\Request;

class UserController extends Controller
{
    public function show(Request $request, int $id)
    {
        return view('users.show', ['user' => User::find($id)]);
    }
}
```

---

### Model

Models represent your database tables and provide an abstraction layer for querying and manipulating records.  
Each model maps to a database table and defines the structure of its data.

**Example Model:**

```php
namespace Http\Model;

use App\Databases\Facade\Model;

class User extends Model
{
    public string $primary_key = 'id';
    public string $table = 'users';
    public array $fillable = ['id', 'name', 'email'];
}
```

---

### Querying with Models

Models provide a simple, expressive interface for database operations.

**Fetch active users:**

```php
$users = User::where('active', 1)->row();
````

**Fetch a single column (by primary key):**

```php
$email = User::_( $user_id )->email;
```

**Check if a record exists:**

```php
$is_exist = User::where('id', $user_id)->exists();
```

**Insert a new record:**

```php
$newUser = User::create([
    'id'    => $user_id,
    'name'  => 'John Doe',
    'email' => 'john.doe@test.com',
]);
```

---

### Using the Database Facade Directly

For advanced queries, you can use the `Database` facade directly.

**Insert a record:**

```php
\App\Databases\Database::create('users', [
    'id'    => $user_id,
    'name'  => 'John Doe',
    'email' => 'john.doe@test.com',
]);
```

**Run a raw query:**

```php
$users = \App\Databases\Database::query("SELECT * FROM users");
```

**Count total records:**

```php
$total = \App\Databases\Database::table('users')->count();
```

**Query from a specific connection:**

```php
$users = \App\Databases\Database::server('master')
    ->query("SELECT * FROM users")
    ->fetch();
```

---

### Schema Builder

The **Schema Builder** provides a programmatic way to create, modify, and manage database tables.  
It works with closures to define table blueprints and runs SQL queries under the hood.

---

### Creating Tables

```php
use App\Databases\Schema;

Schema::create('users', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
});
````

---

### Modifying Tables

```php
Schema::table('users', function ($table) {
    $table->string('phone')->nullable();
});
```

---

### Renaming and Dropping Tables

```php
// Rename table
Schema::renameTable('users', 'members');

// Drop table if it exists
Schema::dropIfExists('sessions');

// Drop table permanently
Schema::drop('logs');
```

---

### Columns Management

```php
// Check if table exists
Schema::hasTable('users');

// Get column info
Schema::column('users', 'email');

// Fetch all columns
Schema::fetchColumns('users');

// Drop a column
Schema::dropColumn('users', 'phone');

// Rename a column
Schema::renameColumn('users', 'fullname', 'name', 'VARCHAR(255)');
```

---

### Indexes and Keys

```php
// Add index
Schema::addIndex('users', 'email');

// Drop index
Schema::dropIndex('users', 'email_index');

// Fetch index details
Schema::index('users', 'email_index');
```

---

### Table Options

```php
// Change storage engine
Schema::setEngine('users', 'InnoDB');

// Change character set and collation
Schema::setCharset('users', 'utf8mb4', 'utf8mb4_unicode_ci');

// Truncate a table
Schema::truncate('users');
```

---

### Export Table Definition

```php
// Get CREATE TABLE statement for export/backup
$definition = Schema::exportTable('users');
```

✅ With `Schema`, you can define migrations, manage schema changes, and keep your database structure consistent across environments.

---

### Views & Blade Templates

The framework uses the **Blade templating engine**:

```blade
<!-- views/welcome.blade.php -->
@extends('layouts')

@section('content')
<h1>Welcome, {{ $name }}!</h1>
@endsection
```

---

### Artisan CLI

The framework includes a CLI tool (`artisan`) for common tasks:

```bash 
php artisan serve 
```
```bash  
php artisan make:controller UserController 
```
```bash 
php artisan make:model User 
```

---

### StreamWire

Build reactive components with **StreamWire** (no JavaScript required):

```php
namespace Components\Test;
	
use App\Utilities\Handler\Component;

class Counter extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    /**
     * Component Lifecycle and Configuration
     *
     * ## Available Methods:
     * - `identifier()` — Requires if we allow the component to be executed on the frontend.
     * - `redirect()` — Performs an Ajax-based redirection.
     * - `init()` — Serves as the component's initializer; use this to set up internal state or dependencies.
     * - `verify()` — (Optional) Runs pre-render validation or checks before displaying the component.
     * - `loader()` — Returns a loading skeleton or placeholder shown while the component is processing.
     *
     * See the component interface located at:
     * @see ./components/Counter/index.blade.php
     */
    public function render()
    {
        return $this->compile([
            'count' => $this->count
        ]);
    }
}
```

```blade
<!-- components/Counter/Counter.blade.php -->
<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment()">+</button>
</div>
```

You can embed reactive components directly inside your Blade views.  
Simply call the `stream()` helper function and pass the component class.

**Example:**

```bladehtml
<div class="container">
    {!! stream(Components\Test\Counter::class) !!}
</div>
```

This will render the **Counter** component and make it fully interactive without writing any JavaScript.

---

## 🔧 Advanced Topics

### Cron Jobs & Scheduler

Run scheduled tasks using the built-in scheduler. Add this to your system crontab:

```bash
* * * * * /usr/bin/php /var/www/html/artisan schedule:run >> /dev/null 2>&1 
```

### Explanation
- `* * * * *` → Run every minute.
- `/usr/bin/php` → Path to your PHP binary (check with `which php`).
- `/var/www/html/scheduler` → Path to your project's `scheduler` file.
- `>> /dev/null 2>&1` → Silences all output (keeps system logs clean).

💡 Note: When using Docker, the cron service is already configured inside the cron container. You don’t need to add this entry manually unless you’re running outside of Docker.

---

### LocalStack & AWS Integration

Use LocalStack to emulate AWS services locally during development.
This allows you to test S3 storage and other AWS features without connecting to a real AWS account.

Create a new bucket:

```bash
docker exec -it localstack_container awslocal s3 mb s3://my-bucket 
```

List existing buckets:

```bash 
docker exec -it localstack_container awslocal s3 ls 
```

💡 Note: LocalStack is intended only for local development and testing.
On a production server, you must configure real AWS credentials and services (e.g., using IAM roles, S3 buckets, etc.).

---

## 🤝 Contributing

Contributions are welcome! Please fork the repository and submit a pull request.

---

## 📜 License

This framework is open-source software licensed under the [MIT license](LICENSE).
