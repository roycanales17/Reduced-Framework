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
![Socket.IO](https://img.shields.io/badge/Socket.IO-010101?logo=socket.io&logoColor=white)
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

## Table of Contents

- [Getting Started](#getting-started)
    - [Installation](#installation)
    - [Server Requirements](#1-server-requirements)
- [Configuration](#️-configuration)
    - [Environment File](#2-environment-file-env)
    - [Docker Setup](#3-docker-setup)
    - [Available Services](#available-services)
- [Framework Overview](#-framework-overview)
    - [Routing](#1-routing)
    - [Cron](#2-cron-scheduler)
    - [Controllers](#3-controllers)
    - [Models & ORM](#4-model)
    - [Schema Builder](#5-schema-builder)
    - [Views & Blade Templates](#6-views--blade-templates)
    - [Artisan CLI](#7-artisan-cli)
    - [StreamWire](#8-streamwire)
- [Advanced Topics](#-advanced-topics)
    - [Real-Time Communication (Socket.IO)](#1-real-time-communication-socketio)
    - [Cron Jobs & Scheduler](#2-cron-jobs--scheduler)
    - [LocalStack & AWS Integration](#3-localstack--aws-integration)
- [Contributing](#-contributing)
- [License](#-license)

---

## Getting Started

### Installation

Install the framework using Composer:

```bash
composer create-project roy404/framework project-name 
```

Once installed, navigate to your project root directory and start the local development server:

```bash 
php artisan serve 
```

---

## ⚙️ Configuration

### 1. Server Requirements
Make sure your system meets the following minimum requirements:

- **PHP** ≥ 8.2
- **Composer** (for dependency management)
- **Docker** & **Docker Compose** (for containerized development)

If PHP or Composer are not installed yet, follow these steps:

#### Install PHP (Mac / Linux)

```bash
sudo apt install php php-cli php-mbstring unzip curl -y
```

or on macOS:

```bash
brew install php
```

Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Verify installation:

```bash
php -v
composer -V
```

#### Windows
1. Download PHP from: https://windows.php.net/download/
2. Add the PHP folder to your system PATH.
3. Download Composer from: https://getcomposer.org/download/
4. Run the installer and follow the prompts.

After installation, open Command Prompt or PowerShell and verify:

```bash
php -v
composer -V
```

If these commands return version numbers, both are installed correctly.

Additionally, If you want to use Docker just install it here: https://www.docker.com/products/docker-desktop

---

### 2. Environment File (.env)
Before running the project, configure your environment settings.

1. Copy the example file if needed:  
   **cp .env.example .env**

2. Update the values as needed — especially:
    - **APP_URL** (e.g. http://localhost:8000)
    - **Database credentials**
    - **Mail settings**
    - **AWS or LocalStack credentials** (if applicable)
    - **PROJECT_ID** — make sure this value is **unique for each project** to avoid Docker container name conflicts.  
      Example:
        - Project 1 → `PROJECT_ID=myproject1`
        - Project 2 → `PROJECT_ID=myproject2`

---

### 3. Docker Setup

Start the development environment using Docker:

```bash
docker-compose up --build -d
```

Once all containers are running, you can access the following services:

| Service | URL | Description |
|---|---:|---|
| 🧱 **App (PHP)** | http://localhost:8000 | Main application |
| 🐘 **phpMyAdmin** | http://localhost:8080 | MySQL database manager |
| 💌 **MailHog UI** | http://localhost:8025 | View test emails sent from the app |
| 🧠 **Redis** | localhost:6379 | In-memory cache database |
| 🧰 **Memcached** | localhost:11211 | Caching service |
| ☁️ **LocalStack** | http://localhost:4566 | Local AWS cloud service emulator |

Tip: You can check logs for any service using  
**docker-compose logs -f <service_name>**  

Example:
```bash 
docker-compose logs -f app
```

---

### Stop and Clean Up

To stop and remove all running containers:

```bash
docker-compose down
```

If you also want to remove associated volumes and networks (fresh start):

```bash
docker-compose down -v
```

---

### Troubleshooting (quick)
- **Mailer / SMTP errors**: Ensure MAIL_HOST is set to `mailhog` (not container_name) when used inside Docker.
- **DB connection issues**: Ensure DB_HOST is `mysql` and the MySQL container is healthy (`docker-compose ps` / `docker-compose logs mysql`).
- **Ports already in use**: Another project may be using the same host ports (e.g., 8000, 8080, 8025).  
  → Change `APP_PORT`, `PMA_PORT`, or other port values in `.env`.

---

### 🧰 Common Commands
- Rebuild & recreate containers:  
  **docker-compose up --build -d**
- Stop & remove containers:  
  **docker-compose down**
- Stop, remove containers + volumes:  
  **docker-compose down -v**
- Follow logs:  
  **docker-compose logs -f <service>**
- Run a shell inside the app container:  
  **docker-compose exec app sh**
- Connect to MySQL (from host):  
  **mysql -h 127.0.0.1 -P 3306 -u <user> -p**

---

✅ **Tip for multiple projects:**  
If you run multiple Docker projects at once, always give each one a **unique `PROJECT_ID`** and different **port numbers** to avoid conflicts.  
Example:
- Project A → `PROJECT_ID=framework1`, `APP_PORT=8000`, `PMA_PORT=8080`
- Project B → `PROJECT_ID=framework2`, `APP_PORT=8100`, `PMA_PORT=8180`

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
- **socket** → Real-time communication service powered by **Node.js** and **Socket.IO**, running on port `3000` (`http://localhost:3000`).

---

## 🏗 Framework Overview

The framework provides a modular architecture with the following core components.

## 1. Routing

Routes are the entry points of your application. They define how incoming HTTP requests (such as `GET`, `POST`, `PUT`, or `DELETE`) are mapped to specific actions in your code — usually a function or a controller method.

Think of them as a **map**:
- The **URL** (e.g., `/users/5`)
- The **HTTP method** (e.g., `GET`)
- The **action** (what your app should do, like showing a user profile)  

Define routes in the `routes/web.php` file:

```php
use App\Routes\Route;
use Http\Controller\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users/{id}', [UserController::class, 'show']);
```

#### Available Routing Methods:

The framework provides a fluent, expressive API for defining routes.  
Below are the available static methods for configuring routes:

| Method | Description |
|---------|-------------|
| `put`(string \$uri, string\|array\|Closure \$action = []) | Defines a `PUT` route. |
| `patch`(string \$uri, string\|array\|Closure \$action = []) | Defines a `PATCH` route. |
| `delete`(string \$uri, string\|array\|Closure \$action = []) | Defines a `DELETE` route. |
| `get`(string \$uri, string\|array\|Closure \$action = []) | Defines a `GET` route. |
| `post`(string \$uri, string\|array\|Closure \$action = []) | Defines a `POST` route. |
| `group`(array \$attributes, Closure \$action) | Registers a group of routes with shared configurations and middleware, enhancing route organization and reusability. |
| `controller`(string \$className) | Registers a controller to handle the route actions. |
| `middleware`(string\|array \$action) | Assigns middleware to the route for request filtering. |
| `prefix`(string \$prefix) | Adds a URI prefix to all routes in the group. |
| `name`(string \$name) | Assigns a name to the route, useful for generating URLs. |
| `domain`(string\|array \$domain) | Binds the route to a specific domain or subdomain. |
| `where`(string \$key, string \$expression) | Defines a regular expression constraint for a route parameter. |

## 2. Cron Scheduler

The **Scheduler** provides a route-like API for defining and managing recurring tasks using cron expressions.  

Instead of handling raw cron jobs directly, you define schedules in a clean and expressive way — similar to defining routes.

All schedules are defined in the `routes/cron.php` file:

### Sample Schedules

```php
use App\Console\Schedule;

Schedule::command('clear:logs')->daily();
Schedule::command('emails:send')->everyFiveMinutes();
Schedule::command('report:generate')->at('14:30');
```

### Available Frequency Methods

The scheduler provides expressive helpers for defining task frequency:

| Method                  | Description                                             |
|--------------------------|---------------------------------------------------------|
| `everyMinute()`          | Run the task every minute.                              |
| `everyFiveMinutes()`     | Run the task every 5 minutes.                           |
| `hourly()`               | Run the task every hour.                                |
| `daily()`                | Run the task daily at midnight.                         |
| `weekly()`               | Run the task weekly on Sunday at midnight.              |
| `monthly()`              | Run the task monthly on the 1st at midnight.            |
| `yearly()`               | Run the task yearly on January 1st at midnight.         |
| `cron($expression)`      | Use a custom cron expression (e.g., `0 6 * * 1-5`).     |
| `at('HH:MM')`            | Run the task daily at a specific time.                  |

## 3. Controllers

Controllers are responsible for handling **request/response logic**. They act as an intermediary between your routes and your business logic, keeping your code organized and maintainable.

You can define controllers in the `http/Controllers` directory:

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

Example Usage:
```php
Route::get('/users/{id}', [Http\Controller\UserController::class, 'show']);
```

---

## 4. Model

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

**Insert a new record:**

```php
$newUser = Http\Model\User::create([
    'name'  => 'John Doe',
    'email' => 'john.doe@test.com',
    'status' => 1
]);
```

**Fetch active users:**

```php
$users = Http\Model\User::where('active', 1)->fetch();
````

**Check if a record exists:**

```php
$isExist = Http\Model\User::where('id', $userId)->exists();
```

**Fetch a single column (by primary key):**

```php
$email = Http\Model\User::_($userId)->email;
```

---

### Using the Database Facade Directly

For advanced queries, you can use the `Database` facade directly.

**Insert a record:**

```php
App\Databases\Database::create('users', [
    'name'  => 'John Doe',
    'email' => 'john.doe@test.com',
    'status' => 1
]);
```

**Run a raw query:**

```php
$users = App\Databases\Database::query("SELECT * FROM users");
```

**Count total records:**

```php
$total = App\Databases\Database::table('users')->count();
```

**Query from a specific connection:**

```php
$users = App\Databases\Database::server('master')
    ->query("SELECT * FROM users")
    ->fetch();
```

---

## 5. Schema Builder

The **Schema Builder** provides a programmatic way to create, modify, and manage database tables.  

It works with closures to define table blueprints and runs SQL queries under the hood.

---

### Creating Tables

```php
use App\Databases\Schema;
use App\Databases\Handler\Blueprints\Table;

Schema::create('users', function (Table $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
});
````

---

### Modifying Tables

```php
use App\Databases\Schema;
use App\Databases\Handler\Blueprints\Table;

Schema::table('users', function (Table $table) {
    $table->string('phone')->nullable();
});
```

---

### Renaming and Dropping Tables

```php
use App\Databases\Schema;

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
use App\Databases\Schema;

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
use App\Databases\Schema;

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
use App\Databases\Schema;

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
$definition = App\Databases\Schema::exportTable('users');
```

✅ With `Schema`, you can define migrations, manage schema changes, and keep your database structure consistent across environments.

---

## 6. Views & Blade Templates

The framework uses the Blade templating engine, which provides a clean and expressive syntax for building your views.
Blade templates are compiled into plain PHP and cached for optimal performance.

```blade
{{-- Escaped output (HTML is escaped) --}}
<p>{{ '<strong>This will not render as bold</strong>' }}</p>

{{-- Unescaped output (HTML will render) --}}
<p>{!! '<strong>This will render as bold text</strong>' !!}</p>

{{-- Conditional statements --}}
@if($isAdmin)
    <p>You have admin access.</p>
@elseif($isUser)
    <p>You are logged in as a regular user.</p>
@else
    <p>Please log in to continue.</p>
@endif

{{-- Loops --}}
<ul>
    @foreach($tasks as $task)
        <li>{{ $loop->iteration }}. {{ $task }}</li>
    @endforeach
</ul>

{{-- Including other templates --}}
@include('partials.footer')

{{-- Components --}}
<x-alert type="success" message="Welcome to the app!" />
```

### Common Blade Directives
| Directives                       |                         Description | Example                                       |
|----------------------------------|------------------------------------:|-----------------------------------------------|
| `{{ $var }}`                     |                      Escaped output | `{{ $user->name }}`                           |
| `{!! $html !!}`                  |     Unescaped output (renders HTML) | `{!! $post->content !!}`                      |
| `@if / @elseif / @else / @endif` |                   Conditional logic | `@if($user)` ... `@endif`                     |
| `@foreach / @endforeach`         | Loop through an array or collection | `@foreach($items as $item)` ... `@endforeach` |
| `@for / @endfor`                 |                      Basic for loop | `@for($i = 0; $i < 5; $i++)` ... `@endfor`    |
| `@extends('layout')`             |                     Extend a layout | `@extends('header')`                          |
| `@csrf`                          |       Insert a CSRF token for forms | `<form>@csrf</form>`                          |
| `@php / @endphp`                 |                            PHP Tags | `@php` $test = "foo"; `@endphp`               |
| `@post`                          |       Grab the POST Global Variable | `@post('email')`                              |


---

## 7. Artisan CLI

The framework includes a powerful command-line interface called Artisan, designed to help you perform common development tasks quickly — such as running servers, managing migrations, creating files, and clearing caches.

You can run Artisan commands using:

```bash
php artisan [command]
```

### Common Commands

```bash
# Start the local development server
php artisan serve
```

```bash
# Display a list of all available commands
php artisan list
```

---

## 8. StreamWire

Build reactive, stateful UI components with **StreamWire** — without writing any JavaScript.  

StreamWire integrates seamlessly with Blade templates, allowing you to render dynamic components directly in your views.

```php
namespace Components\Counter;
	
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

Example content `components/Counter/index.blade.php`:

```bladehtml
<div class="container">
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

### Common Stream-Wire Element Attributes Action

| Directives                    |                                                        Description | Example                                                      |
|-------------------------------|-------------------------------------------------------------------:|--------------------------------------------------------------|
| `wire:model`                  | Two-way data binding between input fields and component properties | `<input type="text" wire:model="name">`                      |
| `wire:click`                  |                                  Trigger an action method on click | `<button wire:click="save()">Save</button>`                  |
| `wire:submit`                 |                       Listen for form submission and call a method | `<form wire:submit="register()">...</form>`                  |
| `wire:keydown.keypress`       |                      Trigger a method on a specific keypress event | `<input wire:keydown.keypress="search(event.target.value)">` |
| `wire:keydown.enter`          |                    Trigger an action method  when Enter is pressed | `<input wire:keydown.enter="submitForm()">`                  |
| `wire:keydown.escape`         |                                Run a method when Escape is pressed | `<input wire:keydown.escape="resetForm()">`                  |
| `wire:loader`                 |        Show or hide elements or more while a request is processing | `<div wire:loader.classList.add="active">Loading...</div>`   |


---

## 🔧 Advanced Topics

### 1. Real-Time Communication (Socket.IO)

To support real-time updates such as live notifications, chat, or dashboard syncing, this project includes a dedicated **Socket.IO** microservice built with **Node.js** and integrated into the Docker environment.

---

#### **Core Features**
- Real-time bi-directional communication
- WebSocket-based events (auto fallback to polling)
- JSON-based event messaging
- Centralized logging for connections and events

---

#### **Technologies**

![Node.js](https://img.shields.io/badge/Node.js-18%2B-339933?logo=node.js&logoColor=white)
![Socket.IO](https://img.shields.io/badge/Socket.IO-010101?logo=socket.io&logoColor=white)
![Express](https://img.shields.io/badge/Express-000000?logo=express&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=white)

---

#### **Docker Service Definition**

To scaffold a ready-made Socket.IO service within your project, run:
```bash
php artisan make:socket
```
This command generates a preconfigured Node.js Socket.IO setup inside your project directory (`/node` by default).

---

Next, register the Socket service in your `docker-compose.yml` file:
```yaml
  node:
      build:
          context: ./node
          dockerfile: Dockerfile
      container_name: ${PROJECT_ID}_socket
      restart: unless-stopped
      ports:
          - "${SOCKET_PORT:-3000}:3000"
      volumes:
          - "./logs/node:/usr/src/app/logs"
      networks:
          - project_network
      env_file:
          - .env
```

Once added, rebuild and start your containers:
```bash 
docker-compose up --build -d 
```
This will build and launch the Socket.IO container, making it accessible at
👉 `http://localhost:3000`

---

#### **Quick Frontend Test**

You can verify the socket connection by embedding the following script in your Blade or HTML view:

```javascript
(function () {
    const script = document.createElement("script");
    script.src = "https://cdn.socket.io/4.7.5/socket.io.min.js";
    script.onload = () => {
        const socket = io("http://localhost:3000");

        socket.on("connect", () => {
            console.log("✅ Connected:", socket.id);
            socket.emit("message", "Hello from frontend");
        });

        socket.on("message", (msg) => {
            console.log("💬 From server:", msg);
        });
    };
    document.head.appendChild(script);
})();
```

When you reload the page, open your browser console — you should see messages confirming a successful connection between the frontend and the Socket.IO server.

---

### 2. Cron Jobs & Scheduler

The project includes a built-in task scheduler for handling automated and recurring jobs (e.g., queue processing, cleanups, reports).

In a **Docker environment**, the scheduler container runs automatically — no additional configuration is required.

However, in a **production environment**, you’ll need to register the scheduler manually in your system crontab to ensure it runs every minute:

```bash
* * * * * /usr/bin/php /var/www/html/artisan cron:scheduler >> /dev/null 2>&1 
```

### Explanation
- `* * * * *` → Run every minute.
- `/usr/bin/php` → Path to your PHP binary (check with `which php`).
- `/var/www/html/artisan` → Path to your project's `artisan` file.
- `>> /dev/null 2>&1` → Silences all output (keeps system logs clean).

---

### 3. LocalStack & AWS Integration

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
