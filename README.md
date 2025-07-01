# PHP FRAMEWORK

Install the bundle using Composer:

```
composer create-project roy404/framework project-name
```

# DOCUMENTATION

The PHP framework is a custom-built solution aimed at enhancing code organization and promoting best practices in Object-Oriented Programming (OOPS) for PHP development. It offers a set of tools and features designed to streamline the development process and improve code maintainability.

![PHP](https://img.shields.io/badge/PHP-8.2%2B-8892BF?logo=php&logoColor=white)
![OOP](https://img.shields.io/badge/OOP-Principles-blue)
![Custom_Framework](https://img.shields.io/badge/Custom_Framework-lightgrey)
![MVC](https://img.shields.io/badge/MVC-Pattern-brightgreen)
![Active_Development](https://img.shields.io/badge/Status-Active-brightgreen)

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

Run the docker image
```docker
docker-compose up --build -d
```