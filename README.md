# PHP FRAMEWORK

Install the bundle using Composer:

```
composer create-project roy404/framework project-name
```

# DOCUMENTATION

The PHP framework is a custom-built solution aimed at enhancing code organization and promoting best practices in Object-Oriented Programming (OOPS) for PHP development. It offers a set of tools and features designed to streamline the development process and improve code maintainability.

### Features

![PHP](https://img.shields.io/badge/PHP-8.2%2B-8892BF?logo=php&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=white)
![SCSS](https://img.shields.io/badge/SCSS-CC6699?logo=sass&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-38B2AC?logo=tailwind-css&logoColor=white)
![Blade](https://img.shields.io/badge/Blade-FF2D20?logo=laravel&logoColor=white)
![Xdebug](https://img.shields.io/badge/Xdebug-2C873F?logo=php&logoColor=white)


### Utilities
![RateLimiter](https://img.shields.io/badge/RateLimiter-Utility-blue)
![Cache](https://img.shields.io/badge/Cache-Utility-blue)
![Request%20Validation](https://img.shields.io/badge/Request_Validation-Utility-blue)
![Routes](https://img.shields.io/badge/Routes-Utility-blue)
![Blade%20Compiler](https://img.shields.io/badge/Blade_Compiler-Utility-blue)
![Eloquent](https://img.shields.io/badge/Eloquent-ORM-purple)
![CLI%20Artisan](https://img.shields.io/badge/CLI_Artisan-Tool-lightgrey)
![StreamWire](https://img.shields.io/badge/StreamWire-Reactive-red)
![Storage](https://img.shields.io/badge/Storage-Utility-blue)
![Logger](https://img.shields.io/badge/Logger-Utility-blue)
![Carbon](https://img.shields.io/badge/Carbon-Datetime-ff69b4)

## Key Components:

- [Artisan](https://github.com/roycanales17/Web-Artisan): This framework includes a command-line interface (CLI) tool to automate repetitive tasks, such as database migrations, seeding, and code generation.
- [Routing](https://github.com/roycanales17/Web-Routes): The routing component provides a flexible and intuitive way to define routes for incoming HTTP requests, allowing developers to map URLs to specific controller actions.
- [Model](https://github.com/roycanales17/Eloquent-Class): The model component offers a convenient way to interact with the database using object-oriented principles, enabling developers to define and manipulate database records as PHP objects.
- [Eloquent](https://github.com/roycanales17/Eloquent-Class): Eloquent is a powerful ORM (Object-Relational Mapping) that simplifies database operations by allowing developers to interact with the database using PHP objects and relationships.
- [Views and Blades](https://github.com/roycanales17/Blades-Compiler): Views are responsible for presenting data to the user, while Blades provide a way to reuse and extend common layout structures across multiple views, promoting code reusability and maintainability.
- [Controller](https://github.com/roycanales17/Web-Utilities): Controllers handle incoming requests, process data from the model, and return responses to the client. They help in separating business logic from presentation logic.
- [Middleware](https://github.com/roycanales17/Web-Utilities): Middleware are filters that can be applied to incoming requests to perform tasks such as authentication, logging, or modifying request data before it reaches the controller.
- [StreamWire](https://github.com/roycanales17/Web-Utilities): Stream wire is a full-stack framework that enables developers to build dynamic, reactive interfaces using PHP instead of JavaScript. It allows components to be rendered and updated on the frontend while keeping the logic in PHP, providing a seamless and efficient way to create interactive web applications. 

## Purpose and Benefits:

The framework aims to improve code organization, maintainability, and scalability of PHP projects by enforcing best practices in OOPS and providing a set of tools to streamline development tasks. It encourages developers to write clean, modular, and reusable code, leading to more robust and maintainable applications.

___

# Project Setup & Usage with Docker

## 1. How to Use Docker
Make sure **Docker and Docker Compose** are installed on your system.

Start the containers with:
```bash
docker-compose up --build -d
```

## 2. How to Use Docker
To verify **Xdebug** is active inside the container:

```bash
docker exec -it app_container tail -f /tmp/xdebug/xdebug.log
```

You should see log output when a request starts (if `xdebug.start_with_request=yes` is enabled).


## 3. PhpStorm Setup for Xdebug

1. Enable Debug Listening
   - In PhpStorm, click the telephone icon in the top-right toolbar so it turns green.
2. Set up Servers
   - Go to: `Preferences → PHP → Servers`
   - Add a server:
     - Host: `localhost` (or the domain you use to access the container)
     - Port: `80`
     - Debugger: `Xdebug`
     - Enable Use path mappings, map your local project root → `/var/www/html` inside the container.
3. Configure Debug Port
   - Go to: `Preferences → PHP → Debug`
   - Set **Debug port**: `9003`
   - Ensure it matches the port in your `xdebug.ini`.


## 4. Running Artisan Inside Docker

Always run Artisan inside the container, not on your host machine:

```bash
docker exec -it app_container php artisan
```

## 5. Stopping and Restarting Containers

To stop everything:

```bash
docker-compose down
```

To rebuild and restart after making changes:
```bash
docker-compose up --build -d
```

## ⏰ Setting up the Cron Job

To enable the application scheduler, add the following line to your system crontab:

```bash
* * * * * /usr/bin/php /www/var/html/artisan >> /dev/null 2>&1
```

### Explanation
- `* * * * *` → Run every minute.
- `/usr/bin/php` → Path to your PHP binary (check with `which php`).
- `/var/www/html/scheduler` → Path to your project's `scheduler` file.
- `>> /dev/null 2>&1` → Silences all output (keeps system logs clean).


# AWS

To create bucket run this command below:
```bash
docker exec -it localstack_container awslocal s3 mb s3://my-bucket
```

Check your bucket:
```bash
docker exec -it localstack_container awslocal s3 ls
```
